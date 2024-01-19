<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Shipping_Pro_License {
	public $id = 'woo_conditional_shipping_pro';
	public $slug = 'woo-conditional-shipping-pro';
	public $plugin_file = WOO_CONDITIONAL_SHIPPING_PRO_FILE; 
	public $plugin = 'woo-conditional-shipping-pro/woo-conditional-shipping-pro.php';
	public $update_url = 'https://wptrio.com/products/conditional-shipping/metadata.json?mac=61b9088853c73cab71cc9d6c6bc87720';
	public $update_checker = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Init update checker
		$this->init_update_checker();

		// Add license to the settings
		add_action( 'woo_conditional_shipping_after_settings', [ $this, 'add_license_settings' ] );

		// Save license
		add_action( 'woocommerce_settings_save_shipping', [ $this, 'save_license' ], 20, 0 );

		// Schedule license ping
		if ( ! wp_next_scheduled ( $this->id . '_license_ping' ) ) {
			wp_schedule_event( time(), 'daily', $this->id . '_license_ping' );
		}

		// Hook into license ping schedule
		add_action( $this->id . '_license_ping', [ $this, 'update_license_status' ] );

		// Deactivation hook for license ping
		register_deactivation_hook( $this->plugin_file, function() {
			wp_clear_scheduled_hook( $this->id . '_license_ping' );
		} );

		// Notification about invalid / expired license
		add_action( 'in_plugin_update_message-' . $this->plugin, [ $this, 'license_notice' ], 10, 2 );
	}

	/**
	 * Init plugin update checker
	 */
	public function init_update_checker() {
		$this->update_checker = Puc_v4_Factory::buildUpdateChecker(
			$this->update_url,
			$this->plugin_file,
			$this->slug
		);

		$this->update_checker->addQueryArgFilter( function( $args ) {
			$args['license_key'] = get_option( 'license_' . $this->id, '' );
			$args['site_url'] = get_site_url();

			if ( function_exists( 'woo_conditional_shipping_get_rulesets' ) ) {
				$args['activity_score'] = count( woo_conditional_shipping_get_rulesets( true ) );
			}
		
			return $args;
		} );
	}

	/**
	 * Notification about invalid / expired license
	 */
	public function license_notice( $plugin_data, $response ) {
		if ( current_user_can( 'update_plugins' ) && empty( $response->package ) ) {
			$license_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_conditional_shipping' ) . '#license_' . $this->id;

			printf( ' <a href="%s" target="_blank">%s &raquo;</a>', $license_url, __( 'Check the license', 'woo-conditional-shipping' ) );
		}
	}

	/**
	 * Add license settings
	 */
	public function add_license_settings( $settings ) {
		$license_key = get_option( 'license_' . $this->id, '' );
		$status = get_option( 'license_' . $this->id . '_status', null );
		$error = get_option( 'license_' . $this->id . '_error', '' );
		$renew_url = get_option( 'license_' . $this->id . '_renew_url', '' );
		$last_checked = get_option( 'license_' . $this->id . '_last_checked', false );
		$status_unknown = ( $status === null );

		include 'views/license.html.php';
	}

	/**
	 * Save license
	 */
	public function save_license() {
		if ( isset( $_POST['license_' . $this->id] ) ) {
			$license_key = $_POST['license_' . $this->id];
			update_option( 'license_' . $this->id, $license_key );

			$this->update_license_status();
		}
	}

	/**
	 * Update license status after saving the settings
	 */
	public function update_license_status() {
		$prev_status = get_option( 'license_' . $this->id . '_status', null );
		$license_key = get_option( 'license_' . $this->id, '' );

		$status = $this->get_license_status( $license_key );

		if ( $status === true ) {
			update_option( 'license_' . $this->id . '_status', '1' );
			update_option( 'license_' . $this->id . '_error', '' );
			update_option( 'license_' . $this->id . '_renew_url', '' );

			// Check updates as well if we had invalid license previously
			if ( $prev_status === '0' && $this->update_checker ) {
				$this->update_checker->checkForUpdates();
			}
		} else {
			update_option( 'license_' . $this->id . '_status', '0' );
			update_option( 'license_' . $this->id . '_error', $status['error'] );

			if ( isset( $status['renew_url'] ) && $status['renew_url'] ) {
				update_option( 'license_' . $this->id . '_renew_url', $status['renew_url'] );
			} else {
				update_option( 'license_' . $this->id . '_renew_url', '' );
			}
		}

		update_option( 'license_' . $this->id . '_last_checked', time() );
	}

	/**
	 * Get license status
	 */
	private function get_license_status( $license_key ) {
		$response = wp_remote_post( 'https://wptrio.com/api/licenses/check', [
			'sslverify' => false,
			'timeout' => 10,
			'body' => [
				'license_key' => $license_key,
				'product' => $this->slug,
				'site_url' => get_site_url(),
				'activity_score' => count( woo_conditional_shipping_get_rulesets( true ) ),
			]
		] );

		if ( ! is_wp_error( $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $response_code === 200 || $response_code === 204 ) {
				return true;
			} else if ( $response_code === 401 ) {
				return [
					'error' => isset( $body->error ) ? $body->error : __( 'N/A', 'woo-conditional-shipping' ),
					'renew_url' => isset( $body->renew_url ) ? $body->renew_url : false,
				];
			}
		} else {
			return [
				'error' => $response->get_error_message(),
			];
		}

		return [
			'error' => __( 'Unknown error', 'woo-conditional-shipping' ),
		];
	}
}
