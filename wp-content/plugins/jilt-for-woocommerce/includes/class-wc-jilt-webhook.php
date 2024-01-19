<?php
/**
 * WooCommerce Jilt
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@jilt.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Jilt to newer
 * versions in the future. If you wish to customize WooCommerce Jilt for your
 * needs please refer to http://help.jilt.com/jilt-for-woocommerce
 *
 * @package   WC-Jilt
 * @author    Jilt
 * @copyright Copyright (c) 2015-2021, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;

/**
 *
 * @since 1.5.0
 */
class WC_Jilt_Webhook {


	/**
	 * Gets all Jilt for WooCommerce webhooks (identified by their delivery URL).
	 *
	 * @since 1.7.8
	 *
	 * @return \WC_Webhook[]
	 */
	public static function get_webhooks() {

		$webhooks = [];

		if ( Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte( '3.3.0' ) ) {

			try {

				$data_store  = \WC_Data_Store::load( 'webhook' );
				$webhook_ids = $data_store->get_webhooks_ids();

			} catch ( Exception $exception ) {

				$webhook_ids = [];
			}

		} else {

			$webhook_ids = get_posts( [
				'fields'         => 'ids',
				'post_type'      => 'shop_webhook',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			] );
		}

		// filter by delivery URL
		foreach ( $webhook_ids as $webhook_id ) {

			try {
				$webhook = new \WC_Webhook( $webhook_id );
			} catch ( Exception $exception ) {
				continue;
			}

			if ( false !== strpos( $webhook->get_delivery_url(), wc_jilt()->get_app_hostname() ) ) {
				$webhooks[] = $webhook;
			}
		}

		return $webhooks;
	}


	/**
	 * Deletes all Jilt for WooCommerce webhooks.
	 *
	 * @since 1.5.0
	 */
	public static function delete_webhooks() {

		$webhooks = self::get_webhooks();

		foreach ( $webhooks as $webhook ) {

			if ( Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte( '3.3.0' ) ) {

				// true to delete permanently
				$webhook->delete( true );

			} else {

				wp_delete_post( $webhook->get_post_data()->ID, true );
			}
		}
	}


}
