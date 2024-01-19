<?php

/**
 * Uninstall Plugin and revoke user license 
 * 
 * @package ShoutOut
 * @since 2.0.0
 */

if( !defined( 'SHOUTOUTGLOBAL_WOO_VERSION' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_VERSION', '4.0.2' );// Plugin Version
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_DIR', dirname( __FILE__ ) );// Plugin dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_URL' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_URL', plugin_dir_url( __FILE__ ) );// Plugin url
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_INC_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_INC_DIR', SHOUTOUTGLOBAL_WOO_DIR . '/includes' );// Plugin include dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_INC_URL' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_INC_URL', SHOUTOUTGLOBAL_WOO_URL . 'includes' );// Plugin include url
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_ADMIN_DIR' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_ADMIN_DIR', SHOUTOUTGLOBAL_WOO_INC_DIR . '/admin' );// Plugin admin dir
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_BASENAME' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_BASENAME', basename( SHOUTOUTGLOBAL_WOO_DIR ) ); // base name
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_META_PREFIX' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_META_PREFIX', '_shoutout_global_' );// Plugin Prefix
}
if( !defined( 'SHOUTOUTGLOBAL_WOO_TEXTDOMAIN' ) ) {
	define( 'SHOUTOUTGLOBAL_WOO_TEXTDOMAIN', 'shoutout-global' );// Plugin Prefix
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

    		global $wpdb;

	  		$access_key = get_site_option( '_store_access_key' );

		    $license_key = !empty( $access_key ) ? esc_attr($access_key) : '';

			$woo_uninstall = SHOUTOUTGLOBAL_WOO_UNINSTALL_URL . $license_key;
			
			try {
				
				$response = wp_remote_get( $woo_uninstall );

				if ( is_multisite() ) {
					$ms_sites = (array) get_sites();

					if ( 0 < count( $ms_sites ) ) {
						foreach ( $ms_sites as $ms_site ) {
							switch_to_blog( $ms_site->blog_id );
							so_delete_key();
							restore_current_blog();
							}
						}
					} else {
						so_delete_key();
					}
				
				
			}
			catch (\RuntimeException $ex) {
				die(sprintf('Http error %s with code %d', $ex->getMessage(), $ex->getCode()));
			}

			function so_delete_key(){

				delete_option( '_store_access_key' );

			}

?>