<?php

/*
Plugin Name: WooCommerce Conditional Shipping Pro
Plugin URI:  https://wptrio.com
Description: Disable shipping methods based on shipping classes, weight, categories and much more.
Version:     2.7.4
Author:      Lauri Karisola / WP Trio
Author URI:  https://wptrio.com
Text Domain: woo-conditional-shipping
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
if ( ! defined( 'WOO_CONDITIONAL_SHIPPING_PRO_VERSION' ) ) {
	define( 'WOO_CONDITIONAL_SHIPPING_PRO_VERSION', '2.7.4' );
}

/**
 * Assets version
 */
if ( ! defined( 'WOO_CONDITIONAL_SHIPPING_ASSETS_VERSION' ) ) {
	define( 'WOO_CONDITIONAL_SHIPPING_ASSETS_VERSION', '2.7.4.pro' );
}

/**
 * Plugin path
 */
if ( ! defined( 'WOO_CONDITIONAL_SHIPPING_PRO_PATH' ) ) {
	define( 'WOO_CONDITIONAL_SHIPPING_PRO_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin file
 */
if ( ! defined( 'WOO_CONDITIONAL_SHIPPING_PRO_FILE' ) ) {
	define( 'WOO_CONDITIONAL_SHIPPING_PRO_FILE', __FILE__ );
}

/**
 * Plugin update checker
 */
require_once 'plugin-update-checker/plugin-update-checker.php';

/**
 * License functionality
 */
include_once( WOO_CONDITIONAL_SHIPPING_PRO_PATH . 'includes/pro/woo-conditional-shipping-pro-license.php' );

/**
 * Load plugin textdomain
 *
 * @return void
 */
add_action( 'plugins_loaded', 'woo_conditional_shipping_pro_load_textdomain' );
function woo_conditional_shipping_pro_load_textdomain() {
  load_plugin_textdomain( 'woo-conditional-shipping', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  load_plugin_textdomain( 'plugin-update-checker', false, dirname( plugin_basename( __FILE__ ) ) . '/plugin-update-checker/languages/' );
}

class Woo_Conditional_Shipping_Pro {
	/**
	 * Constructor
	 */
	function __construct() {
		if ( ! defined( 'WOO_CONDITIONAL_SHIPPING_BASENAME' ) ) {
			define( 'WOO_CONDITIONAL_SHIPPING_BASENAME', plugin_basename( __FILE__ ) );
		}

		// WooCommerce not activated, abort
		if ( ! defined( 'WC_VERSION' ) ) {
			return;
		}

		$this->includes();
	}

	/**
	 * Include required files
	 */
	public function includes() {
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-conditional-shipping-updater.php' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-woo-conditional-shipping-debug.php' );
		Woo_Conditional_Shipping_Debug::instance();

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-conditional-shipping-filters.php' );
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/pro/class-conditional-shipping-filters-pro.php' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-woo-conditional-shipping-post-type.php', 'Woo_Conditional_Shipping_Post_Type' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/class-woo-conditional-shipping-ruleset.php', 'Woo_Conditional_Shipping_Ruleset' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/woo-conditional-shipping-utils.php' );

		if ( is_admin() ) {
			$this->admin_includes();
		}

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/frontend/class-woo-conditional-shipping-frontend.php', 'Woo_Conditional_Shipping_Frontend' );

		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/pro/class-conditional-shipping-pro-functions.php', 'Woo_Conditional_Shipping_Pro_Functions' );
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/pro/woo-conditional-shipping-pro-license.php', 'Woo_Conditional_Shipping_Pro_License' );
	}

	/**
	 * Include admin files
	 */
	private function admin_includes() {
		$this->load_class( plugin_dir_path( __FILE__ ) . 'includes/admin/class-woo-conditional-shipping-admin.php', 'Woo_Conditional_Shipping_Admin' );
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

function init_woo_conditional_shipping_pro() {
	new Woo_Conditional_Shipping_Pro();
}
add_action( 'plugins_loaded', 'init_woo_conditional_shipping_pro', 100 );
