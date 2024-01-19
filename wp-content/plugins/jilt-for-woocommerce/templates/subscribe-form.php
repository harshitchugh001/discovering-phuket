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

/**
 * Renders the Jilt signup / subscribe form.
 *
 * @type bool $show_names whether first / last name fields should be shown
 * @type bool $require_names whether first / last name fields are required
 * @type string $button_text the subscribe button text
 * @type array $list_ids the Jilt list IDs the contact will be added to
 * @type array $tags the Jilt tags that will be added to the contact
 * @type array $contact_data contact properties {
 *   @type null|string $first_name the contact first name
 *   @type null|string $last_name the contact last name
 *   @type null|string $email the contact email
 * }
 *
 * @version 1.7.1
 * @since 1.7.0
 */

defined( 'ABSPATH' ) or exit;

do_action( 'wc_jilt_before_subscribe_form' ); ?>

<div class="u-column2 col-2 jilt-for-woocommerce subscribe-form woocommerce">

	<?php do_action( 'wc_jilt_subscribe_form_start' ); ?>

	<form method="post" class="wc_jilt_subscribe_form" action="#wc_jilt_subscribe_form" <?php do_action( 'wc_jilt_subscribe_form_tag' ); ?>>

		<?php if ( $show_names ) : ?>

			<p class="woocommerce-FormRow woocommerce-FormRow--first form-row form-row-first">
				<label for="wc_jilt_fname_<?php echo esc_attr( $form_id ); ?>"><?php esc_html_e( 'First name', 'jilt-for-woocommerce' ); ?><?php echo $require_names ? ' <span class="required">*</span>' : ''; ?></label>
				<input id="wc_jilt_fname_<?php echo esc_attr( $form_id ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="wc_jilt_fname" value="<?php if ( $contact_data['first_name'] ) echo esc_attr( $contact_data['first_name'] ); ?>" <?php echo $require_names ? ' required' : ''; ?>/>
			</p>

			<p class="woocommerce-FormRow woocommerce-FormRow--last form-row form-row-last">
				<label for="wc_jilt_lname_<?php echo esc_attr( $form_id ); ?>"><?php esc_html_e( 'Last name', 'jilt-for-woocommerce' ); ?><?php echo $require_names ? ' <span class="required">*</span>' : ''; ?></label>
				<input id="wc_jilt_lname_<?php echo esc_attr( $form_id ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="wc_jilt_lname" value="<?php if ( $contact_data['last_name'] ) echo esc_attr( $contact_data['last_name'] ); ?>" <?php echo $require_names ? ' required' : ''; ?>/>
			</p>

		<?php endif; ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wc_jilt_email_<?php echo esc_attr( $form_id ); ?>"><?php esc_html_e( 'Email address', 'jilt-for-woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input id="wc_jilt_email_<?php echo esc_attr( $form_id ); ?>" type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="wc_jilt_email" autocomplete="email" value="<?php if ( $contact_data['email'] ) echo esc_attr( $contact_data['email'] ); ?>" />
		</p>

		<?php // Spam Trap ?>
		<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="email_second"><?php esc_html_e( 'Anti-spam', 'jilt-for-woocommerce' ); ?></label><input type="text" name="email_second" tabindex="-1" autocomplete="off" /></div>

		<p class="woocommerce-FormRow form-row">
			<?php wp_referer_field(); ?>
			<input type="hidden" name="wc_jilt_subscribe_list_ids" value="<?php echo esc_attr( $list_ids ); ?>" />
			<input type="hidden" name="wc_jilt_subscribe_tags" value="<?php echo esc_attr( $tags ); ?>" />
			<button type="submit" class="woocommerce-Button button" name="wc_jilt_subscribe" value="<?php echo esc_attr( $button_text ); ?>"><?php echo esc_html( $button_text ); ?></button>
		</p>

	</form>

	<?php do_action( 'wc_jilt_subscribe_form_end' ); ?>

</div>

<?php do_action( 'wc_jilt_after_subscribe_form' );
