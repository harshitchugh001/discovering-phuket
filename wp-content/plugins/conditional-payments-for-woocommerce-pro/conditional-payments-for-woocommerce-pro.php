<?php

/*
Plugin Name: Conditional Payments for WooCommerce Pro
Description: Disable payment methods based on shipping methods, customer address and much more.
Version:     2.5.1
Author:      Lauri Karisola / WooElements.com
Author URI:  https://wooelements.com
Text Domain: woo-conditional-payments
Domain Path: /languages
WC requires at least: 3.0.0
WC tested up to: 6.0.0
*/

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version
 */
if ( ! defined( 'CONDITIONAL_PAYMENTS_FOR_WOO_PRO_VERSION' ) ) {
	define( 'CONDITIONAL_PAYMENTS_FOR_WOO_PRO_VERSION', '2.5.1' );
}

/**
 * Assets version
 */
if ( ! defined( 'WOO_CONDITIONAL_PAYMENTS_ASSETS_VERSION' ) ) {
	define( 'WOO_CONDITIONAL_PAYMENTS_ASSETS_VERSION', '2.5.1.pro' );
}

/**
 * Plugin file
 */
if ( ! defined( 'WOO_CONDITIONAL_PAYMENTS_PRO_FILE' ) ) {
	define( 'WOO_CONDITIONAL_PAYMENTS_PRO_FILE', __FILE__ );
}

/**
 * Plugin update checker
 */
require_once 'plugin-update-checker/plugin-update-checker.php';
$wcp_update_checker = Puc_v4_Factory::buildUpdateChecker(
	'http://wooelements.com/products/24/metadata?mac=5811853d679a27b5fabc39415dc1ab10',
	__FILE__,
	'conditional-payments-for-woocommerce-pro'
);
$wcp_update_checker->addQueryArgFilter( function( $args ) {
	$args['license_key'] = get_option( 'license_conditional_payments_for_woocommerce_pro', '' );
	$args['site_url'] = get_site_url();

	return $args;
} );

/**
 * Load plugin textdomain
 *
 * @return void
 */
add_action( 'plugins_loaded', 'woo_conditional_payments_pro_load_textdomain' );
function woo_conditional_payments_pro_load_textdomain() {
  load_plugin_textdomain( 'woo-conditional-payments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

class Woo_Conditional_Payments_Pro {
	function __construct() {
		// WooCommerce not activated, abort
		if ( ! defined( 'WC_VERSION' ) ) {
			return;
		}

		if ( ! defined( 'WOO_CONDITIONAL_PAYMENTS_BASENAME' ) ) {
			define( 'WOO_CONDITIONAL_PAYMENTS_BASENAME', plugin_basename( __FILE__ ) );
		}

		$this->includes();
	}

	/**
	 * Include required files
	 */
	public function includes() {
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-woo-conditional-payments-updater.php' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-conditional-payments-filters.php' );
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/pro/class-conditional-payments-filters-pro.php' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-woo-conditional-payments-post-type.php', 'Woo_Conditional_Payments_Post_Type' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-woo-conditional-payments-ruleset.php', 'Woo_Conditional_Payments_Ruleset' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/woo-conditional-payments-utils.php' );

		if ( is_admin() ) {
			$this->admin_includes();
		}

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/frontend/class-woo-conditional-payments-frontend.php', 'Woo_Conditional_Payments_Frontend' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/pro/class-conditional-payments-pro-functions.php', 'Woo_Conditional_Payments_Pro_Functions' );
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/pro/woo-conditional-payments-pro-license.php', 'Woo_Conditional_Payments_Pro_License' );
	}

	/**
	 * Include admin files
	 */
	private function admin_includes() {
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/admin/class-woo-conditional-payments-admin.php', 'Woo_Conditional_Payments_Admin' );
	}

	/**
	 * Load class
	 */
	private function load_class( $filepath, $class_name = FALSE ) {
		require_once( $filepath );

		if ( $class_name ) {
			return new $class_name;
		}

		return TRUE;
	}
}

function init_woo_conditional_payments_pro() {
	new Woo_Conditional_Payments_Pro();
}
add_action( 'plugins_loaded', 'init_woo_conditional_payments_pro', 100 );
