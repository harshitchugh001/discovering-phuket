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
 * @package   WC-Jilt/API
 * @author    Jilt
 * @copyright Copyright (c) 2015-2021, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;

/**
 * The WC REST API Jilt order status controller.
 *
 * This class adds routes for reading the status of an order, as needed by Jilt.
 * This is an intentionally minimal endpoint, providing only the data needed by Jilt for performance reasons.
 *
 * @since 1.7.1
 */
class WC_Jilt_REST_Order_Status_Controller extends WC_REST_Controller {


	/** @var string endpoint namespace */
	protected $namespace = 'wc/v2';

	/** @var string the route base */
	protected $rest_base = 'jilt/order-status';


	/**
	 * Registers the routes for the order status endpoint.
	 *
	 * @since 1.7.1
	 */
	public function register_routes() {

		// get the order status
		register_rest_route( $this->namespace, "/{$this->rest_base}", [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			],
		] );
	}


	/**
	 * Checks if a given request has access to read the order status.
	 *
	 * @since 1.7.1
	 *
	 * @param \WP_REST_Request $request request object
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! wc_rest_check_post_permissions( 'shop_order', 'read' ) ) {
			return new \WP_Error( 'wc_jilt_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'jilt-for-woocommerce' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}


	/**
	 * Gets the order status.
	 *
	 * This is an intentionally minimal response to maximize performance.
	 *
	 * @since 1.7.1
	 *
	 * @param \WP_REST_Request $request request object
	 * @return \WP_REST_Response|\WP_Error order status or error
	 */
	public function get_items( $request ) {

		$jilt_cart_token = $request->get_param( 'jilt_cart_token' );
		$jilt_order_id   = $request->get_param( 'jilt_id' );
		$order_id        = $request->get_param( 'order_id' );
		$result          = null;

		if ( null === $order_id ) {

			if ( null !== $jilt_cart_token ) {
				$order_id = $this->get_order_id_for_jilt_cart_token( $jilt_cart_token );
			} elseif ( null !== $jilt_order_id ) {
				$order_id = $this->get_order_id_for_jilt_order_id( $jilt_order_id );
			}
		}

		if ( null !== $order_id ) {

			$order_meta = $this->get_meta_data_for_order_id( $order_id );

			if ( ! empty( $order_meta ) ) {

				$result = [
					'order_id'       => $order_id,
					'date_completed' => isset( $order_meta['_date_completed'] ) ? wc_rest_prepare_date_response( $order_meta['_date_completed'], false ) : null,
					'jilt'           => [
						'order_id'     => isset( $order_meta['_wc_jilt_order_id'] ) ? $order_meta['_wc_jilt_order_id'] : null,
						'cart_token'   => isset( $order_meta['_wc_jilt_cart_token'] ) ? $order_meta['_wc_jilt_cart_token'] : null,
						'placed_at'    => isset( $order_meta['_wc_jilt_placed_at'] ) ? date( 'Y-m-d\TH:i:s\Z', $order_meta['_wc_jilt_placed_at'] ) : null,
						'cancelled_at' => isset( $order_meta['_wc_jilt_cancelled_at'] ) ? date( 'Y-m-d\TH:i:s\Z', $order_meta['_wc_jilt_cancelled_at'] ) : null,
					]
				];
			}
		}

		$result = null === $result ? new \WP_Error( 'wc_jilt_rest_no_order_status_found', __( 'No order status found', 'jilt-for-woocommerce' ) ) : $result;

		return rest_ensure_response( $result );
	}


	/**
	 * Gets order id for the provided cart token.
	 *
	 * @param string $jilt_cart_token
	 * @return int|null order id, if found, null otherwise
	 *@since 1.7.1
	 */
	private function get_order_id_for_jilt_cart_token( $jilt_cart_token ) {
		global $wpdb;

		$result = $wpdb->get_var( $wpdb->prepare( "
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wc_jilt_cart_token'
			AND meta_value = %s
		", $jilt_cart_token ) );

		return null === $result ? null : (int) $result;
	}


	/**
	 * Gets the order ID for the provided Jilt order ID.
	 *
	 * @param string $jilt_order_id
	 * @return int|null order id, if found, null otherwise
	 *@since 1.7.1
	 */
	private function get_order_id_for_jilt_order_id( $jilt_order_id ) {
		global $wpdb;

		$result = $wpdb->get_var( $wpdb->prepare( "
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wc_jilt_order_id'
			AND meta_value = %s
		", $jilt_order_id ) );

		return null === $result ? null : (int) $result;
	}


	/**
	 * Gets order meta data for the order ID.
	 *
	 * @since 1.7.1
	 *
	 * @param int $order_id the order ID
	 * @return array of meta_key => meta_value
	 */
	private function get_meta_data_for_order_id( $order_id ) {
		global $wpdb;

		$results = [];
		$query   = $wpdb->get_results( $wpdb->prepare( "
			SELECT meta_key, meta_value
			FROM {$wpdb->postmeta}
			WHERE post_id = %s
		", $order_id ), ARRAY_A );

		if ( ! empty( $query ) ) {

			foreach ( $query as $result ) {

				$results[ $result['meta_key'] ] = $result['meta_value'];
			}
		}

		return $results;
	}


}
