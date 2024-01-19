<?php
/*
Plugin Name: ShoutOut
Plugin URI: https://wordpress.org/plugins/shoutout
Description: ShoutOut is a software as a service (SaaS) and is a popular affiliate and multi level marketing solution that allows tracking of affiliates.
Version: 4.0.2
Author: Database Plus
Author URI: https://www.shoutout.global
Text Domain: shoutout-global
Domain Path:
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/

You can contact us at support@shoutout.global

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 * 
 * @package ShoutOut
 * @since 1.0.0
 */
if( !defined( 'SHOUTOUTGLOBAL_WOO_VERSION' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_VERSION', '4.0.2' );// Plugin Version
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_DIR', plugin_dir_path( __FILE__ ) );// Plugin dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_DIR', plugin_dir_path( __FILE__ ) );// Plugin dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_URL' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_URL', plugin_dir_url( __FILE__ ) );// Plugin url
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_INC_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_INC_DIR', SHOUTOUTGLOBAL_WOO_DIR . 'includes/' );// Plugin include dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_INC_URL' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_INC_URL', SHOUTOUTGLOBAL_WOO_URL . 'includes' );// Plugin include url
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_ADMIN_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_ADMIN_DIR', SHOUTOUTGLOBAL_WOO_INC_DIR . 'admin' );// Plugin admin dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_BASENAME' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_BASENAME', basename( SHOUTOUTGLOBAL_WOO_DIR ) ); // base name
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_META_PREFIX' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_META_PREFIX', '_shoutout_global_' );// Plugin Prefix
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_TEXTDOMAIN' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_TEXTDOMAIN', 'shoutout-global');// Plugin Prefix
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_REST_URL' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_REST_URL', 'https://www.shoutout.global/access?enc=' ); //'https://www.shoutout.global/createSOUser' // REST URL
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_STORE_LINK' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_STORE_LINK', 'https://www.shoutout.global/access?enc=' );
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_UNINSTALL_URL' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_UNINSTALL_URL', 'https://www.shoutout.global/woouninstall?UserID=' );// Uninstall URL
}


/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package ShoutOut
 * @since 1.0.0
 */
function shoutout_global_load_textdomain() {
	
	// Set filter for plugin's languages directory
	$shoutout_global_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$shoutout_global_lang_dir	= apply_filters( 'shoutout_global_languages_directory', $shoutout_global_lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), SHOUTOUTGLOBAL_WOO_TEXTDOMAIN );
	$mofile	= sprintf( '%1$s-%2$s.mo', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN, $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $shoutout_global_lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . SHOUTOUTGLOBAL_WOO_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/shoutout folder
		load_textdomain( SHOUTOUTGLOBAL_WOO_TEXTDOMAIN, $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/shoutout/languages/ folder
		load_textdomain( SHOUTOUTGLOBAL_WOO_TEXTDOMAIN, $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( SHOUTOUTGLOBAL_WOO_TEXTDOMAIN, false, $shoutout_global_lang_dir );
	}
}

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 * modified 11-02-2021 for multisite
 * @package ShoutOut
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'shoutout_global_activate' );

function shoutout_global_activate( $network_wide ) {
		
		// added by sabweb 02/21 for multisite

		if ( is_multisite() && $network_wide ) {
			$ms_sites = (array) get_sites();
			// var_dump($ms_sites);
			if ( 0 < count( $ms_sites ) ) {
				foreach ( $ms_sites as $ms_site ) {
					switch_to_blog( $ms_site->blog_id );
					shoutout_activated();
					restore_current_blog();
				}
			}
		} else {
			shoutout_activated();
		}
	}

/**
 * Perform plugin activation tasks
 *
 * @since 4.0.0
 *
 * 
 */

// added by sabweb 02/21 for multisite

function shoutout_activated() {
	delete_option( '_store_access_key' );
}

//add action to load plugin
add_action( 'plugins_loaded', 'shoutout_global_plugin_loaded' );

/**
 * 
 * Check if WooCommerce is activated
 * 
 * @package ShoutOut
 * @since 1.0.0
 */
function shoutout_global_wc_check() {
	if ( class_exists( 'woocommerce' ) ) {
		global $shoutout_global_wc_active;
		$shoutout_global_wc_active = 'yes';
	} else {
		global $shoutout_global_wc_active;
		$shoutout_global_wc_active = 'no';
	}
}
// add action for check woocommerce is activated or not
add_action( 'admin_init', 'shoutout_global_wc_check' );

/**
 * 
 * Show admin notice if WooCommerce is not activated
 * 
 * @package ShoutOut
 * @since 1.0.0
 */
function shoutout_global_wc_admin_notice() {

	global $shoutout_global_wc_active;
	if ( $shoutout_global_wc_active == 'no' ) {
		?>

		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'WooCommerce is not activated, please activate it to use <b>ShoutOut</b>', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ); ?> </p>
		</div>
		<?php
	}
}
// add action for show admin notice message
add_action( 'admin_notices', 'shoutout_global_wc_admin_notice' );

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package ShoutOut
 * @since 1.0.0
 */
function shoutout_global_plugin_loaded() {
	
	if( class_exists( 'Woocommerce' ) ) { //check Woocommerce is activated or not
		
		//Gets the plugin ready for translation
		shoutout_global_load_textdomain();
		
		
		
		
		// Global variables
		global $shoutout_global_scripts, $shoutout_global_public, $shoutout_global_admin;
		
		// Include Misc Functions File
		include_once( SHOUTOUTGLOBAL_WOO_INC_DIR.'/shoutout-global-misc-functions.php' );
		
		// Script class handles most of script functionalities of plugin
		include_once( SHOUTOUTGLOBAL_WOO_INC_DIR.'/class-shoutout-global-scripts.php' );
		$shoutout_global_scripts = new ShoutOut_Global_Scripts();
		$shoutout_global_scripts->add_hooks();

		// Public class handles most of public functionalities of plugin
		include_once( SHOUTOUTGLOBAL_WOO_INC_DIR.'/class-shoutout-global-public.php' );
		$shoutout_global_public = new ShoutOut_Global_Public();
		$shoutout_global_public->add_hooks();

		// Public class handles most of public functionalities of plugin
		include_once( SHOUTOUTGLOBAL_WOO_ADMIN_DIR.'/class-shoutout-global-admin.php' );
		$shoutout_global_admin = new ShoutOut_Global_Admin();
		$shoutout_global_admin->add_hooks();
	}
}

/**
 * Install on new blog
 * 
 * Installs instance on new blog (if mutlisite and network activated)
 * 
 * @package ShoutOut
 * @since 4.0.0
 */
// added by sabweb 02/21 for multisite


add_action( 'wpmu_new_blog', 'new_blog', 10, 6); 		

function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;

	if (is_plugin_active_for_network('shoutout/shoutout.php')) {
		$old_blog = $wpdb->blogid;
		switch_to_blog($blog_id);
		shoutout_activated();
		switch_to_blog($old_blog);
	}
}