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
 * @package   WC-Jilt/Admin/Views
 * @author    Jilt
 * @category  Admin
 * @copyright Copyright (c) 2015-2021, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Jilt admin screen wrapper.
 *
 * @since 1.7.0
 * @version 1.7.0
 *
 * @type array $instance widget instance
 * @type array $lists Jilt lists
 */
?>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'jilt-for-woocommerce' ) ?>:</label>
		<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( ( isset( $instance['title'] ) ) ? $instance['title'] : __( 'Join our mailing list', 'jilt-for-woocommerce' ) ); ?>" />
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'use_names' ) ); ?>"><?php esc_html_e( 'Ask for first and last name?', 'jilt-for-woocommerce' ); ?></label>
		<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'use_names' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'use_names' ) ); ?>">
			<option value="no" <?php selected( isset( $instance['use_names'] ) ? $instance['use_names'] : '', 'no' ); ?>><?php esc_html_e( 'Hide name fields', 'jilt-for-woocommerce' ); ?></option>
			<option value="offer" <?php selected( isset( $instance['use_names'] ) ? $instance['use_names'] : '', 'offer' ); ?>><?php esc_html_e( 'Offer name fields', 'jilt-for-woocommerce' ); ?></option>
			<option value="require" <?php selected( isset( $instance['use_names'] ) ? $instance['use_names'] : '', 'require' ); ?>><?php esc_html_e( 'Require name fields', 'jilt-for-woocommerce' ); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'list_ids' ) ); ?>"><?php esc_html_e( 'Lists', 'jilt-for-woocommerce' ); ?></label>

		<select class="widefat wc-jilt-enhanced-select wc-jilt-lists-selector"
		        id="<?php echo esc_attr( $this->get_field_id( 'list_ids' ) ); ?>"
		        name="<?php echo esc_attr( $this->get_field_name( 'list_ids' ) ); ?>[]"
		        style="width: 100%;"
		        multiple="multiple"
		        data-placeholder="<?php esc_html_e( 'Choose a list&hellip;', 'jilt-for-woocommerce' ); ?>"
		>

			<?php if ( ! empty( $lists ) ) : ?>
				<?php foreach ( $lists as $list_id => $list_name ) : ?>
					<?php $selected = isset( $instance['list_ids'] ) && is_array( $instance['list_ids'] ) && in_array( $list_id, $instance['list_ids'] ); ?>
					<option value="<?php echo esc_attr( $list_id ); ?>" <?php selected( $selected ); ?>><?php echo esc_html( $list_name ); ?></option>
				<?php endforeach; ?>
			<?php endif;?>

		</select>
		<small><?php esc_html_e( 'Select lists customers should be added to in Jilt (optional)', 'jilt-for-woocommerce' ); ?></small>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>"><?php esc_html_e( 'Tag new subscribers', 'jilt-for-woocommerce' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tags' ) ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['tags'] ) ? $instance['tags'] : '' ); ?>" />
		<small><?php esc_html_e( 'Enter a comma-separated list of tags (optional)', 'jilt-for-woocommerce' ); ?></small>
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php esc_html_e( 'Submit button text', 'jilt-for-woocommerce' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" type="text" value="<?php echo esc_attr( isset( $instance['button_text'] ) ? $instance['button_text'] : __( 'Subscribe', 'jilt-for-woocommerce' ) ); ?>" />
	</p>

<?php
