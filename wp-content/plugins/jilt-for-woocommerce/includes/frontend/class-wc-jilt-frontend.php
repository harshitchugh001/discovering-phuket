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
 * @package   WC-Jilt/Frontend
 * @author    Jilt
 * @category  Frontend
 * @copyright Copyright (c) 2015-2021, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use Jilt\WooCommerce\Contacts\WC_Contact;
use SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;

/**
 * Frontend Class
 *
 * Handles post-checkout registration process to show prompts to guest purchasers, and
 * create accounts for them with one click after purchasing.
 *
 * @since 1.3.0
 */
class WC_Jilt_Frontend {


	/**
	 * WC_Jilt_Frontend constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

		add_shortcode( 'jilt_subscribe', [ $this, 'add_shortcode' ] );

		// show a data collection notice when users log in
		if ( wc_jilt()->get_integration()->is_jilt_connected() && wc_jilt()->get_integration()->show_email_usage_notice() ) {

			add_action( 'wp_footer', array( $this, 'output_logged_in_data_notice_html' ) );
		}

		if ( wc_jilt()->get_integration()->allow_post_checkout_registration() ) {

			// maybe render the prompt on the "thank you" page
			// we don't use the woocommerce_thankyou action as we can't consistently add a notice for immediate display
			add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'maybe_render_account_prompt' ), 10, 2 );

			// if the registration link is clicked, validate and register the customer
			add_action( 'template_redirect', array( $this, 'maybe_register_new_customer' ) );

			// add login form fields to indicate when we should link previous orders
			add_action( 'woocommerce_login_form', array( $this, 'add_login_form_fields' ) );

			// if the link orders link is clicked, potentially link previous orders
			add_action( 'wp_login', array( $this, 'link_previous_orders' ), 10, 2 );
		}

		// handle the subscribe form AJAX submit
		add_action( 'wp_ajax_wc_jilt_widget_subscribe',        [ $this, 'ajax_process_widget_subscribe' ] );
		add_action( 'wp_ajax_nopriv_wc_jilt_widget_subscribe', [ $this, 'ajax_process_widget_subscribe' ] );
	}


	/**
	 * Loads front end styles.
	 *
	 * @since 1.4.5
	 */
	private function load_styles() {

		if ( wc_jilt()->get_integration()->show_email_usage_notice() ) {
			wp_enqueue_style( 'wc-jilt', wc_jilt()->get_plugin_url() . '/assets/css/frontend/wc-jilt-frontend.min.css', array(), wc_jilt()->get_version() );
		}
	}


	/**
	 * Returns the storefront JS script url
	 *
	 * @since 1.5.6
	 *
	 * @return string 'https://js.jilt.com/storefront/v1/jilt.js' by default
	 */
	public function get_storefront_js_url() {

		/**
		 * Filters the storefront js script url
		 *
		 * @since 1.5.6
		 *
		 * @param string storefront JS script url
		 * @param \WC_Jilt_Frontend instance
		 */
		return apply_filters( 'wc_jilt_storefront_js_url', 'https://js.jilt.com/storefront/v1/jilt.js', $this );
	}


	/**
	 * Loads front end scripts.
	 *
	 * @since 1.4.5
	 */
	private function load_scripts() {

		// add JS for subscribe form; used for widget and shortcode, so always load it
		wp_enqueue_script( 'wc-jilt-subscribe-form', wc_jilt()->get_plugin_url() . '/assets/js/frontend/wc-jilt-subscribe-form.min.js', [ 'jquery', 'woocommerce' ], wc_jilt()->get_version(), true  );

		wp_localize_script( 'wc-jilt-subscribe-form', 'wc_jilt_subscribe', [
			'loader' => wc_jilt()->get_framework_assets_url() . '/images/ajax-loader.gif',
		] );

		// only load javascript once
		if ( ! wp_script_is( 'wc-jilt', 'enqueued' ) ) {

			wp_enqueue_script( 'wc-jilt', $this->get_storefront_js_url(), array(), wc_jilt()->get_version(), true );

			$params = wc_jilt()->get_integration()->get_storefront_params();

			// as a precaution, strip any unsafe params from frontend display
			$params = $this->get_safe_frontend_params( $params );

			// convert yes/no values into booleans
			foreach ( $params as $key => $value ) {

				if ( in_array( $value, array( 'yes', 'no' ), true ) ) {
					$params[ $key ] = 'yes' === $value;
				}
			}

			// script data, including the Storefront params
			$params = array_merge( array(
				'public_key'              => wc_jilt()->get_integration()->get_public_key(),
				'order_address_mapping'   => WC_Jilt_Order::get_jilt_order_address_mapping(),
				'cart_hash'               => wc_jilt()->get_cart_handler_instance()->get_cart_hash(),
				'cart_token'              => WC_Jilt_Session::get_cart_token(),
				'ajax_url'                => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'nonce'                   => wp_create_nonce( 'jilt-for-wc' ),
				'log_threshold'           => wc_jilt()->get_logger()->get_threshold(),
				'x_jilt_shop_domain'      => wc_jilt()->get_shop_domain(),
				'shop_uuid'               => wc_jilt()->get_integration()->get_linked_shop_uuid(),
				'show_email_usage_notice' => wc_jilt()->get_integration()->show_email_usage_notice(),
				'popover_dismiss_message' => $this->get_popover_dismiss_message(),
				'platform'                => 'woocommerce',
				'api_url'                 => sprintf( 'https://%s', wc_jilt()->get_api_hostname() ),
				'tracking_elem_selector'  => '#' . wc_jilt()->get_cart_handler_instance()->get_tracking_element_id(),
			), $params );

			if ( null !== WC_Jilt_Session::get_customer_email_collection_opt_out() ) {
				$params['email_collection_opt_out'] = (bool) WC_Jilt_Session::get_customer_email_collection_opt_out();
			}

			if ( wc_jilt()->get_integration()->capture_email_on_add_to_cart( 'frontend' ) ) {

				/**
				 * Filters the popover title for the add to cart email capture form.
				 *
				 * @since 1.4.0
				 *
				 * @param string $title the popover title
				 */
				$params['add_to_cart_title'] = apply_filters( 'wc_jilt_add_to_cart_popover_title', __( 'Reserve this item in your cart!', 'jilt-for-woocommerce' ) );

			} else {

				$params['capture_email_on_add_to_cart'] = false;
			}

			wp_localize_script( 'wc-jilt', 'jiltStorefrontParams', $params );
		}
	}


	/**
	 * Filters storefront params for what is safe to display on the frontend.
	 *
	 * @since 1.6.4
	 *
	 * @param array $params storefront params
	 * @return array
	 */
	private function get_safe_frontend_params( $params ) {

		$params = wc_jilt()->get_integration()->get_safe_settings( $params );

		unset(
			$params['billing_needs_attention'],
			$params['managed_email_notifications']
		);

		return $params;
	}


	/**
	 * Enqueues front end scripts and styles.
	 *
	 * @internal
	 *
	 * @since 1.4.5
	 */
	public function enqueue_scripts_styles() {

		if ( wc_jilt()->get_integration()->is_jilt_connected() ) {

			$this->load_styles();

			$this->load_scripts();
		}
	}


	/**
	 * Renders the Jilt subscribe form.
	 *
	 * @since 1.7.0
	 *
	 * @param array $atts the shortcode attributes
	 * @return string buffered shortcode contents
	 */
	public function add_shortcode( $atts ) {

		$a = shortcode_atts( [
			'show_names'    => 'no',
			'require_names' => 'no',
			'button_text'   => __( 'Subscribe', 'jilt-for-woocommerce' ),
			'list_ids'      => '',
			'tags'          => '',
		], $atts );

		ob_start();

		wc_jilt_subscribe_form( [
			'show_names'    => 'yes' === $a['show_names'],
			'require_names' => 'yes' === $a['require_names'],
			'button_text'   => $a['button_text'],
			'list_ids'      => array_map( 'trim', explode( ',', $a['list_ids'] ) ),
			'tags'          => array_map( 'trim', explode( ',', $a['tags'] ) ),
		] );

		return ob_get_clean();
	}


	/**
	 * Renders the logged in user data collection notice one time, if enabled.
	 *
	 * @since 1.4.5
	 */
	public function output_logged_in_data_notice_html() {

		$user_id = get_current_user_id();

		// check if we should be showing a logged in notice; don't show it to shop employees
		if (      $user_id > 0
		     && ! current_user_can( 'manage_woocommerce' )
		     &&   wc_jilt()->get_integration()->show_email_usage_notice() ) {

			$opt_out = WC_Jilt_Session::get_customer_email_collection_opt_out( $user_id );

			// only render the notice if this meta has not been set previously
			if ( null === $opt_out ) {

				/* translators: Placeholders: %1$s, %3$s - opening HTML <a> link tag, %2$s - closing HTML </a> link tag */
				$message = sprintf( esc_html__( 'Your cart is saved while logged in so we can send you email reminders about it. %1$sGot it!%2$s %3$sNo thanks.%2$s', 'jilt-for-woocommerce' ),
					'<a href="#" class="dismiss-link">','</a>', '<a href="#" class="logged-in-notice js-wc-jilt-email-collection-opt-out">'
				);

				echo '<div class="woocommerce wc-jilt wc-jilt-email-usage-notice">' . $message . '</div>';

				// once we've shown this notice, set the meta to false (opted in)
				WC_Jilt_Session::set_customer_email_collection_opt_out( false, $user_id );
			}
		}
	}


	/**
	 * Returns the email usage notice.
	 *
	 * @since 1.4.5
	 *
	 * @param array $link_classes classes to add to the usage notice link
	 * @return string HTML
	 */
	public function get_email_usage_notice( $link_classes = array() ) {

		$link_classes[] = 'js-wc-jilt-email-collection-opt-out';

		$notice = sprintf(
			/* translators: Placeholders: %1$s - opening HTML <a> link tag, %2$s - closing HTML </a> link tag */
			__( 'Your email and cart are saved so we can send you email reminders about this order. %1$sNo thanks%2$s.', 'jilt-for-woocommerce' ),
			'<a href="#" class="' . esc_attr( implode( ' ', $link_classes ) ) . '">', '</a>'
		);

		/**
		 * Filters the email usage notice contents.
		 *
		 * @since 1.4.5
		 *
		 * @param string $notice notice text
		 * @param string $link_classes the CSS classes the opt out link should have
		 */
		return (string) apply_filters( 'wc_jilt_email_usage_notice', $notice, $link_classes );
	}



	/**
	 * Gets the text for the add-to-cart popover dismiss message.
	 *
	 * @since 1.5.4
	 *
	 * @return string
	 */
	public function get_popover_dismiss_message( ) {

		if ( wc_jilt()->get_integration()->show_email_usage_notice() ) {

			$message = $this->get_email_usage_notice( array( 'js-jilt-popover-bypass' ) );

		} else {

			$message = wc_jilt()->get_integration()->get_storefront_param('popover_dismiss_message', __( "No thanks, I'll enter my email later.", 'jilt-for-woocommerce' ) );

			/**
			 * Filters the "enter it later" link text for the add to cart email capture form.
			 *
			 * @since 1.4.0
			 *
			 * @param string $dismiss_text cart popover dismissal text
			 */
			$message = apply_filters( 'wc_jilt_add_to_cart_popover_dismiss_text', $message );
		}

		return $message;
	}



	/**
	 * Checks the WooCommerce thankyou page to render registration or login prompt immediately.
	 *
	 * @since 1.3.0
	 *
	 * @param string $text the thankyou page message text
	 * @param \WC_Order $order the placed order object
	 * @return string the updated text
	 */
	public function maybe_render_account_prompt( $text, $order ) {

		// sanity check & send away!
		if ( $order instanceof WC_Order ) {

			$existing_user = get_user_by( 'email', $order->get_billing_email() );

			if ( ! is_user_logged_in() ) {

				// do not use a nonce, favoring order-specific validation
				// this way, a user can't just get a valid nonce, then change the order ID in the registration link
				if ( ! $token = $order->get_meta( '_wc_jilt_post_checkout_registration' ) ) {

					$token = wc_jilt()->generate_random_token( 32 );

					$order->update_meta_data( '_wc_jilt_post_checkout_registration', $token );
					$order->save_meta_data();
				}

				$message = $existing_user ? $this->render_link_order_prompt( $order, $token ) : $this->render_registration_prompt( $order, $token );
				$text    = $message . $text;
			}
		}

		return $text;
	}


	/**
	 * Renders a prompt to log in to link this existing order.
	 *
	 * @since 1.3.0
	 *
	 * @param \WC_Order $order the currently placed order
	 * @param string $token the login token to prompt linking old orders
	 * @return string the login prompt message
	 */
	protected function render_link_order_prompt( $order, $token ) {

		$url = add_query_arg(
			[
				'link_order_id' => $order->get_id(),
				'login_token'   => $token,
			],
			trailingslashit( wc_get_page_permalink( 'myaccount' ) )
		);

		$message  = __( 'Looks like you already have an account! You can link this order to it by clicking here to log in:', 'jilt-for-woocommerce' );
		$message .= ' <a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Log in', 'jilt-for-woocommerce' ) . '</a>';

		return "<div class='woocommerce-info'>{$message}</div>";
	}


	/**
	 * Outputs hidden fields to POST the login token and associated order.
	 *
	 * @since 1.3.0
	 */
	public function add_login_form_fields() {

		if ( ! isset( $_GET['link_order_id'], $_GET['login_token'] ) ) {
			return;
		}

		$order_id = (int) $_GET['link_order_id'];
		$token    = wc_clean( $_GET['login_token'] );

		ob_start();

		?>
		<p class="form-row">
			<input class="woocommerce-Input input-hidden" type="hidden" name="wc_jilt_link_order_id" id="wc_jilt_link_order_id" value="<?php echo esc_attr( $order_id ); ?>" />
			<input class="woocommerce-Input input-hidden" type="hidden" name="wc_jilt_login_token" id="wc_jilt_login_token" value="<?php echo esc_attr( $token ); ?>" />
		</p>
		<?php

		echo ob_get_clean();
	}


	/**
	 * Links previous orders upon customer login
	 *
	 * @since 1.3.0
	 *
	 * @param string $username the username, unused
	 * @param \WP_User $user the logged in user
	 */
	public function link_previous_orders( $username, $user ) {

		// ensure all data is set
		if ( ! isset( $_POST['wc_jilt_link_order_id'], $_POST['wc_jilt_login_token'] ) ) {
			return;
		}

		$order_id = (int) $_POST['wc_jilt_link_order_id'];
		$token    = wc_clean( $_POST['wc_jilt_login_token'] );
		$order    = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			wc_add_notice( __( 'Error linking your previous order.', 'jilt-for-woocommerce' ), 'error' );
			return;
		}

		$stored_token = $order->get_meta( '_wc_jilt_post_checkout_registration' );

		// check the token in the URL with the order's stored token
		if ( ! $stored_token || $token !== $stored_token ) {
			wc_add_notice( __( 'Error linking your previous order.', 'jilt-for-woocommerce' ), 'error' );
			return;
		}

		// We're clear! Link this order and previous ones to the account
		wc_update_new_customer_past_orders( $user->ID );

		/* translators: Placeholders: %s - order number */
		wc_add_notice( sprintf( __( 'Order #%s has been linked to your account!', 'jilt-for-woocommerce' ), $order->get_order_number() ), 'success' );
	}


	/**
	 * Renders the registration prompt on the thankyou page
	 *
	 * @since 1.3.0
	 *
	 * @param \WC_Order $order the order object
	 * @param string $token the registration token for the order
	 * @return string the message to render
	 */
	protected function render_registration_prompt( $order, $token ) {

		$url = add_query_arg(
			[
				'registration_order_id' => $order->get_id(),
				'registration_token'    => $token,
			],
			trailingslashit( wc_get_page_permalink( 'myaccount' ) )
		);

		$message  = __( 'Ensure checkout is fast and easy next time! Create an account and we\'ll save your address details from this order.', 'jilt-for-woocommerce' );
		$message .= ' <a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Create Account', 'jilt-for-woocommerce' ) . '</a>';

		return "<div class='woocommerce-info'>{$message}</div>";
	}


	/**
	 * Registers a new customer if "create" link is valid.
	 *
	 * @since 1.3.0
	 */
	public function maybe_register_new_customer() {

		if ( ! is_account_page() || ! isset( $_REQUEST['registration_order_id'] ) ) {
			return;
		}

		// now we have the order ID param, but not a token, boot this faker!
		if ( ! isset( $_REQUEST['registration_token'] ) ) {
			wc_add_notice( __( 'Whoops, looks like this registration link is not valid.', 'jilt-for-woocommerce' ), 'error' );
			return;
		}

		$order_id = (int) $_REQUEST['registration_order_id'];
		$token    = wc_clean( $_REQUEST['registration_token'] );

		try {

			$user = $this->process_post_checkout_registration( $order_id, $token );

			/* translators: Placeholder: %1$s - first name, %2$s - <a> tag, %3$s - </a> tag */
			wc_add_notice( sprintf( __( 'Welcome, %1$s! Your %2$saccount information%3$s has been saved.', 'jilt-for-woocommerce' ),
					$user->first_name,
					'<strong><a href="' . wc_get_endpoint_url( 'edit-address' ) . '">',
					'</a></strong>'
				), 'success' );

			return;

		} catch ( Framework\SV_WC_Plugin_Exception $e ) {

			wc_add_notice( $e->getMessage(), 'error' );
			return;
		}
	}


	/**
	 * Validate the create account token for the order, and create a customer if valid.
	 *
	 * @since 1.3.0
	 *
	 * @param int $order_id ID of the order ID we should pull customer info for
	 * @param string $token the registration token to validate for the order
	 * @throws Framework\SV_WC_Plugin_Exception when the user can't be created
	 * @return WP_User the newly created user
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	protected function process_post_checkout_registration( $order_id, $token ) {

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			throw new Framework\SV_WC_Plugin_Exception( __( 'This order does not exist; it may have been deleted. Please register manually.', 'jilt-for-woocommerce' ) );
		}

		$stored_token = $order->get_meta( '_wc_jilt_post_checkout_registration' );

		// check the token in the URL with the order's stored token
		if ( ! $stored_token || $token !== $stored_token ) {
			throw new Framework\SV_WC_Plugin_Exception( __( 'Invalid registration link. Please register manually.', 'jilt-for-woocommerce' ) );
		}

		$email = $order->get_billing_email();

		// prep any consent or opt-out values that are in the session for transfer to the new user
		$email_collection_opt_out = WC_Jilt_Session::get_customer_email_collection_opt_out();
		$marketing_consent        = WC_Jilt_Session::get_customer_marketing_consent();

		/**
		 * Fires before creating a new customer via the Order Received page.
		 *
		 * @since 1.3.0
		 *
		 * @param int $order_id the order ID
		 * @param string $email the billing email for the new customer
		 */
		do_action( 'wc_jilt_before_post_checkout_registration', $order_id, $email );

		// force username + password generation
		add_filter( 'pre_option_woocommerce_registration_generate_username', [ $this, 'force_userdata_generation' ] );
		add_filter( 'pre_option_woocommerce_registration_generate_password', [ $this, 'force_userdata_generation' ] );

		$user_id = wc_create_new_customer( $email );

		if ( is_wp_error( $user_id ) ) {
			throw new Framework\SV_WC_Plugin_Exception( $user_id->get_error_message() );
		}

		// stop forcing
		remove_filter( 'pre_option_woocommerce_registration_generate_username', [ $this, 'force_userdata_generation' ] );
		remove_filter( 'pre_option_woocommerce_registration_generate_password', [ $this, 'force_userdata_generation' ] );

		wp_set_current_user( $user_id );
		wc_set_customer_auth_cookie( $user_id );

		// multisite: ensure user exists on current site, if not, add them before allowing login
		if ( $user_id && is_multisite() && is_user_logged_in() && ! is_user_member_of_blog() ) {
			add_user_to_blog( get_current_blog_id(), $user_id, 'customer' );
		}

		// link this order to the customer
		$order->set_customer_id( $user_id );
		$order->save();

		// security note: don't link previous orders automatically here, as someone *could* checkout with another
		// person's email and use this flow, gaining access to the previous purchase history. For privacy, we
		// don't want to then give them access to all previous orders placed with this initial registration.

		// save the customer data from the order
		$this->add_customer_data( $user_id, $order );

		if ( null !== $email_collection_opt_out ) {
			WC_Jilt_Session::set_customer_email_collection_opt_out( $email_collection_opt_out, $user_id );
		}

		if ( null !== $marketing_consent ) {
			WC_Jilt_Session::set_customer_marketing_consent( $marketing_consent, $user_id );
		}

		$user = get_userdata( $user_id );

		/** this hook is documented in wp-includes/user.php */
		do_action( 'wp_login', $user->user_login, $user );

		/**
		 * Fires after creating a new customer via the Order Received page.
		 *
		 * @since 1.3.0
		 *
		 * @param int $order_id the order ID
		 * @param \WP_User $user the newly created user
		 */
		do_action( 'wc_jilt_after_post_checkout_registration', $order_id, $user );

		return $user;
	}


	/**
	 * Save customer's user data from the order.
	 *
	 * We're using usermeta functions here since the customer functions were added in WC 3.0+
	 *
	 * @since 1.3.0
	 *
	 * @param int $user_id the user ID to which we should add data
	 * @param \WC_Order $order the order from which we're pulling customer data
	 */
	protected function add_customer_data( $user_id, $order ) {

		$address_fields = [
			'first_name',
			'last_name',
			'company',
			'phone',
			'address_1',
			'address_2',
			'postcode',
			'city',
			'state',
			'country',
		];

		// core WP Fields
		update_user_meta( $user_id, 'first_name', $order->get_billing_first_name() );
		update_user_meta( $user_id, 'last_name', $order->get_billing_last_name() );

		// WC customer fields
		update_user_meta( $user_id, 'paying_customer', 1 );

		// carry marketing consent from order meta to user meta
		if ( 'yes' === $order->get_meta( '_wc_jilt_marketing_consent_offered' ) ) {

			$marketing_consent = 'yes' === $order->get_meta( '_wc_jilt_marketing_consent_accepted' );

			WC_Jilt_Session::set_customer_marketing_consent( $marketing_consent, $user_id );
		}

		foreach ( $address_fields as $field ) {

			if ( is_callable( [ $order, "get_billing_{$field}" ] ) ) {

				update_user_meta( $user_id, "billing_{$field}", $order->{"get_billing_{$field}"}() );
			}

			if ( 'phone' !== $field && is_callable( [ $order, "get_shipping_{$field}" ] ) ) {

				update_user_meta( $user_id, "shipping_{$field}", $order->{"get_shipping_{$field}"}() );
			}
		}
	}


	/**
	 * Force generata a username or password for a new customer
	 *
	 * @since 1.3.0
	 *
	 * @return string Always 'yes'
	 */
	public function force_userdata_generation() {
		return 'yes';
	}


	/**
	 * Processes the widget AJAX subscribe.
	 *
	 * @internal
	 *
	 * @since 1.7.0
	 */
	public function ajax_process_widget_subscribe() {

		// send an error for honeypot submissions
		if ( ! empty( $_POST['honeypot'] ) ) {

			wp_send_json_error( '<div class="woocommerce"><div class="woocommerce-error">' . __( 'Oops, something went wrong. Please try again.', 'jilt-for-woocommerce' ) . '</div></div>' );
		}

		// set base details
		$button = ! empty( $_POST['button'] ) ? wc_clean( $_POST['button'] ) : __( 'Subscribe', 'jilt-for-woocommerce' );
		$email  = ! empty( $_POST['email'] )  ? wc_clean( $_POST['email'] )  : null;
		$fname  = ! empty( $_POST['fname'] )  ? wc_clean( $_POST['fname'] )  : null;
		$lname  = ! empty( $_POST['lname'] )  ? wc_clean( $_POST['lname'] )  : null;
		$lists  = ! empty( $_POST['lists'] )  ? explode( ',', wc_clean( $_POST['lists'] ) ) : [];
		$tags   = ! empty( $_POST['tags'] )   ? explode( ',', wc_clean( $_POST['tags'] ) )  : [];

		if ( ! is_email( $email ) ) {

			wp_send_json_error( '<div class="woocommerce"><div class="woocommerce-error">' . __( 'Please enter a valid email address.', 'jilt-for-woocommerce' ) . '</div></div>' );

		} else {

			$contact    = new WC_Contact( get_current_user_id() );
			$ip_address = class_exists( '\\WC_Geolocation' ) ? \WC_Geolocation::get_ip_address() : false;

			$contact->set_email( $email );

			if ( $fname ) $contact->set_first_name( $fname );
			if ( $lname ) $contact->set_last_name( $lname );

			$success = $contact->subscribe( $lists, $tags );

			if ( $success['result'] ) {

				// store some local Jilt information
				if ( ! $contact->is_guest() ) {

					// reinstantiate the contact so we only update a few details permanently
					// e.g., don't re-set email address or name
					$contact = new WC_Contact( get_current_user_id() );

					$contact->store_opt_in_details( 'wc_jilt_signup_form', $button, $ip_address );
					$contact->set_jilt_remote_id( $success['message']->id );
				}

				// set the data in the Jilt session for guests; resaves for logged in users
				// but we should separate that from session handling in the future
				\WC_Jilt_Session::set_customer_marketing_consent( true );

				wp_send_json_success( '<div class="woocommerce"><div class="woocommerce-message">' . __( 'Thanks for subscribing!', 'jilt-for-woocommerce' ) . '</div></div>' );

			} else {

				wp_send_json_error( '<div class="woocommerce"><div class="woocommerce-error">' . __( 'Oops, something went wrong. Please try again.', 'jilt-for-woocommerce' ) . '</div>' );

				wc_jilt()->log( sprintf( 'Widget signup error: %s', $success['message'] ) );
			}
		}
	}


}
