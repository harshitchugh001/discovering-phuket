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

namespace Jilt\WooCommerce;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;

/**
 * The Jilt cron class.
 *
 * {BR 2019-09-28} When we require WC 3.6+, we should look to replace this with
 *  Action Scheduler events instead, it was a bit too much to bundle AS for.
 *
 * @since 1.7.0
 */
class Cron {


	/** @var \WC_Jilt_Admin_Status instance */
	protected $admin_status;


	/**
	 * Cron class constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param \WC_Jilt_Admin_Status $admin_status instance
	 */
	public function __construct( \WC_Jilt_Admin_Status $admin_status ) {

		$this->admin_status = $admin_status;

		// schedule coupon cleanup
		add_action( 'init', [ $this, 'schedule_coupon_cleanup' ] );

		// clean up expired, unused coupons
		add_action( 'wc_jilt_coupon_cleanup', [ $this, 'cleanup_coupons' ] );
	}


	/**
	 * Add the scheduled coupon cleanup event.
	 *
	 * @internal
	 *
	 * @since 1.7.0
	 */
	public function schedule_coupon_cleanup() {

		if ( ! wp_next_scheduled( 'wc_jilt_coupon_cleanup' ) ) {

			// using a core schedule, don't forget to add a custom interval if we change this
			wp_schedule_event( strtotime( 'now +5 minutes' ), 'twicedaily', 'wc_jilt_coupon_cleanup' );
		}
	}


	/**
	 * Runs the scheduled cleanup task.
	 *
	 * @internal
	 *
	 * @since 1.7.0
	 */
	public function cleanup_coupons() {

		if ( $this->admin_status ) {

			$this->admin_status->run_delete_coupons_tool();

			// schedule another run if we still have coupons to clear
			if ( $this->has_jilt_coupons() ) {

				wp_schedule_single_event( strtotime( 'now +5 minutes' ), 'wc_jilt_coupon_cleanup' );
			}
		}
	}


	/**
	 * Check if there are Jilt coupons on the site.
	 *
	 * @since 1.7.0
	 *
	 * @return bool true if the site has coupons
	 */
	protected function has_jilt_coupons() {

		$coupon_ids = get_posts( [
			'fields'         => 'ids',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key'     => 'jilt_discount_id',
					'compare' => 'EXISTS',
				],
				[
					'key'   => 'usage_count',
					'value' => '0',
					'type'  => 'NUMERIC',
				],
				[
					'key'     => 'date_expires',
					'value'   => strval( time() ),
					'compare' => '<',
					'type'    => 'NUMERIC',
				],
			],
		] );

		return ! empty ( $coupon_ids );
	}


}
