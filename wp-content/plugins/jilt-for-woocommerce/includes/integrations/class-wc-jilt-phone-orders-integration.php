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
 * @package   WC-Jilt/Integrations
 * @author    Jilt
 * @category  Frontend
 * @copyright Copyright (c) 2015-2021, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Adds support for the Phone Orders for WooCommerce plugin.
 *
 * @since 1.7.8
 */
class WC_Jilt_Phone_Orders_Integration extends \WC_Jilt_Integration_Base {


	/** @var string channel name for Phone Orders */
	const CHANNEL_NAME = 'phone-orders';


	/**
	 * Setups the Phone Orders for WooCommerce integration class.
	 *
	 * @since 1.7.8
	 */
	public function __construct() {

		add_filter( 'wc_jilt_order_channel_name', [ $this, 'filter_order_channel_name' ], 10, 2 );
		add_filter( 'wc_jilt_order_cart_params',  [ $this, 'filter_order_cart_params' ], 10, 2 );
	}


	/**
	 * Gets the title for this integration.
	 *
	 * @see WC_Jilt_Integration::get_title()
	 *
	 * @since 1.7.8
	 *
	 * @return string
	 */
	public function get_title() {

		return __( 'Phone Orders', 'jilt-for-woocommerce' );
	}


	/**
	 * Changes the channel name included in Jilt update requests for orders created using Phone Orders for WooCommerce.
	 *
	 * @internal
	 *
	 * @since 1.7.8
	 *
	 * @param string $channel_name the channel name
	 * @param \WC_Jilt_Order $order the order object
	 * @return string
	 */
	public function filter_order_channel_name( $channel_name, $order ) {

		if ( $order instanceof \WC_Jilt_Order && $order->meta_exists( '_wpo_order_creator' ) ) {
			$channel_name = self::CHANNEL_NAME;
		}

		return $channel_name;
	}


	/**
	 * Adds the channel name to the cart data if the cart was created by Phone Orders for WooCommerce.
	 *
	 * @internal
	 *
	 * @since 1.7.8
	 *
	 * @param array $params
	 * @return array
	 */
	public function filter_order_cart_params( $params ) {

		if ( defined( 'WC_PHONE_CUSTOMER_COOKIE' ) && '' !== WC_PHONE_CUSTOMER_COOKIE ) {
			if ( isset( $_COOKIE[ WC_PHONE_CUSTOMER_COOKIE ] ) ) {
				$params['created_via'] = self::CHANNEL_NAME;
			}
		}

		return $params;
	}


}
