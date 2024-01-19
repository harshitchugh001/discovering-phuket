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
 * Returns the One True Instance of Jilt for WC
 *
 * @since 1.0.0
 *
 * @return \WC_Jilt
 */
function wc_jilt() {

	return WC_Jilt::instance();
}


/**
 * Renders the Jilt subscribe form.
 *
 * @since 1.7.0
 *
 * @param array $args the form arguments {
 *  @type bool $show_names whether first / last name fields should be shown
 *  @type bool $require_names whether first / last name fields are required
 *  @type string $button_text the subscribe button text
 *  @type array $list_ids the Jilt list IDs the contact will be added to
 *  @type array $tags the Jilt tags that will be added to the contact
 * }
 */
function wc_jilt_subscribe_form( $args ) {

	$form = wp_parse_args( $args, [
		'show_names'    => false,
		'require_names' => false,
		'button_text'   => __( 'Subscribe', 'jilt-for-woocommerce' ),
		'list_ids'      => [],
		'tags'          => [],
	] );

	$fname = is_user_logged_in() ? wp_get_current_user()->user_firstname : null;
	$lname = is_user_logged_in() ? wp_get_current_user()->user_lastname  : null;
	$email = is_user_logged_in() ? wp_get_current_user()->user_email     : null;

	// favor data a contact may have submitted over stored data
	$form['contact_data'] = [
		'first_name' => ! empty( $_POST['wc_jilt_fname'] ) ? $_POST['wc_jilt_fname'] : $fname,
		'last_name'  => ! empty( $_POST['wc_jilt_lname'] ) ? $_POST['wc_jilt_lname'] : $lname,
		'email'      => ! empty( $_POST['wc_jilt_email'] ) ? $_POST['wc_jilt_email'] : $email,
	];

	$form['list_ids']   = is_array( $form['list_ids'] ) ? implode( ',', $form['list_ids'] ) : $form['list_ids'];
	$form['tags']       = is_array( $form['tags'] ) ? implode( ',', $form['tags'] ) : $form['tags'];
	$form['form_id']    = uniqid();

	wc_get_template( 'subscribe-form.php', $form, '', wc_jilt()->get_plugin_path() . '/templates/' );
}


/**
 * Returns all order meta keys added by Jilt for WC.
 *
 * @since 1.6.2
 *
 * @return array all meta keys
 */
function wc_jilt_get_order_meta_keys() {

	return [
		'_wc_jilt_cart_token',
		'_wc_jilt_order_id',
		'_wc_jilt_placed_at',
		'_wc_jilt_cancelled_at',
		'_wc_jilt_marketing_consent_offered',
		'_wc_jilt_marketing_consent_accepted',
		'_wc_jilt_marketing_consent_notice',
	];
}
