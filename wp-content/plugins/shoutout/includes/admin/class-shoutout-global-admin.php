<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Pages Class
 * 
 * Handles all the different features and functions
 * for the admin pages.
 * 
 * @package ShoutOut
 * @since 1.0.0
 */

if( !class_exists( 'ShoutOut_Global_Admin' ) ) { // If class not exist
	
	class ShoutOut_Global_Admin {
		
		public function shoutout_globalshoutout_global__construct() {
			
		}

		/**
		 * Add Admin Hook
		 * 
		 * Handle to add admin hooks
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function shoutout_global_shoutout_menus() {

			add_menu_page( __( 'ShoutOut', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ), __( 'ShoutOut', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ), 'manage_options', 'shoutout-global-setting-form', array( $this, 'shoutout_global_setting_form' ) );
		}

		/**
		 * 
		 * Register form
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function shoutout_global_setting_form() {
			include_once( SHOUTOUTGLOBAL_WOO_ADMIN_DIR . '/form/shoutout-global-setting-form.php' );
		}

		/**
		 * Add Admin Hook
		 * 
		 * Handle to add admin hooks
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function add_hooks() {
			
			// add action for save product type post
			add_action( 'admin_menu', array( $this, 'shoutout_global_shoutout_menus' ) );
		}
	}
}