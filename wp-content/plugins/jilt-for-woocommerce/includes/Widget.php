<?php
/**
 * Jilt for WooCommerce
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
 * A simple widget for displaying a subscribe form for Jilt.
 *
 * @since 1.7.0
 */
class Widget extends \WP_Widget {


	/**
	 * Setup the widget options
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		// set widget options
		$options = [
			'classname'   => 'widget_wc_jilt',
			'description' => __( 'Allow your customers to opt into Jilt emails.', 'jilt-for-woocommerce' ),
		];

		// instantiate the widget
		parent::__construct( 'wc_jilt', __( 'Jilt', 'jilt-for-woocommerce' ), $options );

		// include widgets in the WC admin screens
		add_filter( 'woocommerce_screen_ids', function( $ids ) {

			$ids[] = 'widgets';
			return $ids;
		} );

		// reload on widget save
		add_action( 'admin_footer',                     [ $this, 'widget_admin_scripts' ] );
		add_action( 'customize_controls_print_scripts', [ $this, 'widget_admin_scripts' ] );
	}


	/**
	 * Renders the widget.
	 *
	 * @see \WP_Widget::widget()
	 *
	 * @since 1.7.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		if ( ! wc_jilt()->get_integration()->get_api() ) {
			return;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title'];
		}

		$form = [
			'show_names'    => 'no' !== $instance['use_names'],
			'require_names' => 'require' === $instance['use_names'],
			'button_text'   => $instance['button_text'],
			'list_ids'      => $instance['list_ids'],
			'tags'          => $instance['tags'],
		];

		wc_jilt_subscribe_form( $form );

		echo $args['after_widget'];
	}


	/**
	 * Updates the widget title & selected options.
	 *
	 * @see \WP_Widget::update()
	 *
	 * @since 1.7.0
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = [
			'title'       => isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '',
			'use_names'   => isset( $new_instance['use_names'] ) ? sanitize_text_field( $new_instance['use_names'] ) : '',
			'button_text' => isset( $new_instance['button_text'] ) ? sanitize_text_field( $new_instance['button_text'] ) : '',
			'list_ids'    => isset( $new_instance['list_ids'] ) ? array_map( 'intval', $new_instance['list_ids'] ) : '',
			'tags'        => isset( $new_instance['tags'] ) ? sanitize_text_field( $new_instance['tags'] ) : '',
		];

		return $instance;
	}


	/**
	 * Renders the admin form for the widget.
	 *
	 * @see \WP_Widget::form()
	 *
	 * @since 1.7.0
	 *
	 * @param array $instance
	 */
	public function form( $instance ) {

		$api = wc_jilt()->get_integration()->get_api();

		if ( ! $api ) {
			?><p><?php printf( esc_html__( 'You must %1$sconnect to Jilt%2$s before using this widget.', 'jilt-for-woocommerce' ), '<a href="' . esc_url( wc_jilt()->get_settings_url() ) . '">', '</a>' ); ?></p><?php
			return;
		}

		try {

			$lists = wp_list_pluck( $api->get_lists(), 'name', 'id' );

		} catch ( Framework\SV_WC_API_Exception $e ) {

			$lists = [ '' => sprintf( __( 'Oops, something went wrong: %s', 'jilt-for-woocommerce' ), $e->getMessage() ) ];
		}

		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'woocommerce_admin' );
		wp_enqueue_script( 'wc-enhanced-select' );

		include( 'admin/views/html-admin-widget-settings.php' );
	}


	/**
	 * Update the widget's list enhanced select after adding / saving.
	 *
	 * @since 1.7.0
	 */
	public function widget_admin_scripts() {
		?>
		<script type="text/javascript">
			jQuery( function( $ ) {

			    function wc_jilt_reload_enhanced_select() {
					$( '#widgets-right .wc-jilt-enhanced-select' ).removeClass( 'wc-jilt-enhanced-select' ).addClass( 'wc-enhanced-select' );
					$( document.body ).trigger( 'wc-enhanced-select-init' );
				}

				// re-initialize on widgets in the main area
				$( document ).on( 'widget-updated widget-added', wc_jilt_reload_enhanced_select );

				wc_jilt_reload_enhanced_select();
			} );
		</script>
		<?php
	}


}
