<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Script Class
 * 
 * Handles all the JS and CSS include
 * on fron and backend
 * 
 * @package ShoutOut
 * @since 1.0.0
 */

if( !class_exists( 'ShoutOut_Global_Scripts' ) ) { // If class not exist
	
	class ShoutOut_Global_Scripts {
		
		public function shoutout_global__construct() {
			
		}
		
		/**
		 * Enqueue Admin Script
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function shoutout_global_admin_scripts() {

			wp_register_script( 'shoutout-global-admin-script', SHOUTOUTGLOBAL_WOO_URL . 'assets/js/shoutout-global-admin.js', array( 'jquery' ), SHOUTOUTGLOBAL_WOO_VERSION, true );
			// wp_register_script( 'shoutout-global-bootstrap-script', SHOUTOUTGLOBAL_WOO_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), SHOUTOUTGLOBAL_WOO_VERSION, true );
			// wp_register_script( 'shoutout-global-bootstrap-validator', SHOUTOUTGLOBAL_WOO_URL . 'assets/js/bootstrap.validator.js', array( 'jquery' ), SHOUTOUTGLOBAL_WOO_VERSION, true );
			// wp_register_style( 'shoutout-global-bootstrap-style', SHOUTOUTGLOBAL_WOO_URL . 'assets/css/bootstrap.min.css', array(), SHOUTOUTGLOBAL_WOO_VERSION );
			wp_register_style( 'shoutout-global-admin-style', SHOUTOUTGLOBAL_WOO_URL . 'assets/css/shoutout-global-admin.css', array(), SHOUTOUTGLOBAL_WOO_VERSION );

			wp_enqueue_script( 'shoutout-global-admin-script' );
			// wp_enqueue_script( 'shoutout-global-bootstrap-script' );
			// wp_enqueue_script( 'shoutout-global-bootstrap-validator' );
			// wp_enqueue_style( 'shoutout-global-bootstrap-style' );
			wp_enqueue_style( 'shoutout-global-admin-style' );

			wp_localize_script( 'shoutout-global-admin-script','ShoutOut_Global_Admin',array(
															'ajaxurl'	=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
														));
		}

		/**
		 * Enqueue Public Script
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function shoutout_global_public_scripts() {

			global $user_ID;

			wp_register_script( 'shoutout-global-shoutout-theme', 'https://www.shoutout.global/js/shoutout_theme.js', array( 'jquery' ), SHOUTOUTGLOBAL_WOO_VERSION, true );
			wp_register_script( 'shoutout-global-shoutout-cart', 'https://www.shoutout.global/js/shoutout_cart.js', array( 'jquery' ), SHOUTOUTGLOBAL_WOO_VERSION, true );

			if (function_exists('is_multisite') && is_multisite()) {
			
				global $wpdb;
		    	$blog_id = $wpdb->blogid;
			
				switch_to_blog($blog_id);
				$access_key = get_option( '_store_access_key' ); 
			
			} else {   
		    
		    	$access_key = get_option( '_store_access_key' ); 
			}

			if( !empty( $access_key ) ) {

				wp_enqueue_script( 'shoutout-global-shoutout-theme' );

				if( is_checkout() ) {

					wp_enqueue_script( 'shoutout-global-shoutout-cart' );
				}

				$store_name 	= $access_key . '.woocommerce.com';

				wp_localize_script( 'shoutout-global-shoutout-theme', 'Shopify', array(
															'ajaxurl'	=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
															'shop'		=> $store_name
														));
			}
		}

		/**
		 * Add Script Hook
		 * 
		 * Handle to add script hooks
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function add_hooks() {
			
			add_action( 'admin_enqueue_scripts', array( $this, 'shoutout_global_admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'shoutout_global_public_scripts' ) );
		}
	}
}