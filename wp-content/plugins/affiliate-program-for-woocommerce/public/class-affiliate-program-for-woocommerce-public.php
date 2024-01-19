<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://eplugins.in/
 * @since      1.0.0
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/public
 */
class Affiliate_Program_For_Woocommerce_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The ID of User
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $user_id;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->user_id     = get_current_user_ID();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Affilate_Program_For_Woocoomerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Affilate_Program_For_Woocoomerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( is_account_page() && is_user_logged_in() ) {
			wp_enqueue_style( 'datatables_css', '//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/affiliate-program-for-woocommerce-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Affilate_Program_For_Woocoomerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Affilate_Program_For_Woocoomerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( is_account_page() && is_user_logged_in() ) {
			wp_enqueue_script( 'ChartJs', 'https://cdn.jsdelivr.net/npm/chart.js@2.8.0', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', array( 'jquery' ), $this->version, false );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/affiliate-program-for-woocommerce-public.js', array( 'jquery', 'ChartJs', 'clipboard' ), $this->version, false );
			wp_register_script( $this->plugin_name . 'fb-js', plugin_dir_url( __FILE__ ) . 'js/affiliate-program-for-woocommerce-public-fb.js', array( 'jquery', 'ChartJs', 'datatables', 'bootstrap' ), $this->version, false );

			$user_id    = get_current_user_ID();
			$afp_js_arr = array(
				'total_earning_label' => esc_html__( 'Total Earning', 'affiliate-program-for-woocommerce' ),
				'total_balance_label' => esc_html__( 'Total Balance', 'affiliate-program-for-woocommerce' ),
				'total_refund_label'  => esc_html__( 'Total Refund', 'affiliate-program-for-woocommerce' ),
				'main_label'          => esc_html__( 'Total Earning In', 'affiliate-program-for-woocommerce' ) . get_woocommerce_currency_symbol(),
				'total_earn'          => $this->apf_get_total_earning( $user_id ),
				'total_balance'       => $this->apf_get_current_balance( $user_id ),
				'total_refund'        => $this->apf_get_total_refunds( $user_id ),
			);
			wp_localize_script( $this->plugin_name, 'afp_js', $afp_js_arr );
		}
	}

	/**
	 * Function for  affiliate section
	 *
	 * @name get_affiliate_section
	 * @since 1.0.0
	 */
	public function get_affiliate_section() {
		$user_ID         = get_current_user_ID();
		$user = get_userdata( $user_ID );
		$user_roles = $user->roles['0'];
		$get_user_role = $this->get_affiliate_user_role();
		if ( empty( $get_user_role ) ) {
			$this->affiliate_show_dashboard();
		} elseif ( ! empty( $get_user_role ) && in_array( $user_roles, $get_user_role ) ) {
			 $this->affiliate_show_dashboard();
		}
	}

	/**
	 * Show the affiliate dashboard
	 *
	 * @since 1.0.0
	 * @name affiliate_show_dashboard
	 */
	public function affiliate_show_dashboard() {
		$user_ID         = get_current_user_ID();
		$text_above_link = get_option( 'affilate_bef_text', false );
		$text_above_link = ! empty( esc_html( $text_above_link ) ) ? esc_html( $text_above_link ) : esc_html__( 'Refer your friends and you’ll earn commission on their purchases', 'affiliate-program-for-woocommerce' );
		$share_html = $this->get_social_sharing_html( $user_ID );
		?>
		<fieldset>
			<p class="affliate_heading"><?php echo esc_html( $text_above_link ); ?></p>
		
				<span class="affliate_link"><?php esc_html_e( 'Affiliate Link: ', 'affiliate-program-for-woocommerce' ); ?></span>
					<p id='apf_copy'><code><?php echo esc_url( $this->get_affiliate_link( $user_ID ) ); ?></code></p>
			<button class="apf_btn_copy" data-clipboard-target="#apf_copy" aria-label="copied">
				<span class=apf_btn_text"><?php esc_html_e( 'Copy', 'affiliate-program-for-woocommerce' ); ?></span>
			</button>
			
		</fieldset>
		<div class='apf_reports'>
		   <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'reports' ) ); ?>" > <?php esc_html_e( 'Reports', 'affiliate-program-for-woocommerce' ); ?> </a>
		</div>
		<fieldset>
			<?php echo wp_kses_post( $share_html ); ?>
		</fieldset>
		<canvas id="myChart"></canvas>
		<?php
	}

	/**
	 * Get affiliate user role
	 *
	 * @since 1.0.0
	 * @name get_affiliate_user_role
	 */
	public function get_affiliate_user_role() {
		$user_role = get_option( 'affiliate_select_role', false );
		return $user_role;
	}

	/**
	 * Get affliate link of the user.
	 *
	 * @name get_referral_link
	 * @since 1.0.0
	 * @param int $user_id User id of the customer.
	 */
	public function get_affiliate_link( $user_id ) {

		if ( ! empty( $user_id ) ) {

			$affiliate_code = get_user_meta( $user_id, 'affiliate_key', true );

			if ( empty( $affiliate_code ) ) {
				$affiliate_code = $this->set_affiliate_key( $user_id );
			}

			$affiliate_key = self::get_affliate_key();

			$affiliate_link = site_url() . '?' . $affiliate_key . '=' . $affiliate_code;
		}
		return $affiliate_link;
	}
	/**
	 * Function is used for the get the key name
	 *
	 * @since 1.0.0
	 * @name get_affliate_key
	 */
	public static function get_affliate_key() {
		$affliate_key = get_option( 'affilate_key_name', 'affiliate_code' );
		return ( ! empty( $affliate_key ) ) ? $affliate_key : 'affiliate_code';
	}

	/**
	 * Get a affliate link.
	 *
	 * @since 1.0.0
	 * @name set_affiliate_key
	 * @param int $user_id   For which the referral link needs to be set.
	 */
	public function set_affiliate_key( $user_id ) {
		$length = $this->get_affiliate_key_length();

		// Generate the affiliate code.
		$affiliate_key = $this->generate_affiliate_code( $length, $user_id );

		update_user_meta( $user_id, 'affiliate_key', $affiliate_key );

		return $affiliate_key;
	}

	/**
	 * Get the length of the affiliate key
	 *
	 * @name get_affiliate_key_length
	 * @since 1.0.0
	 */
	public function get_affiliate_key_length() {

		$length = get_option( 'affilate_key_length', false );

		return ( ! empty( $length ) ) ? $length : 7;
	}

	/**
	 * Generate for affliate code.
	 *
	 * @name generate_affiliate_code
	 * @param int $length length of the affiliate code.
	 * @param int $user_id Id of the Wp_User.
	 */
	public function generate_affiliate_code( $length, $user_id ) {
		$affiliate_code = '';

		$keys = array_merge( range( 0, 9 ), range( 'A', 'Z' ) );

		$keys[] = $user_id;

		for ( $i = 0; $i < $length; $i++ ) {
			$affiliate_code .= $keys[ array_rand( $keys ) ];
		}

		return $affiliate_code;
	}

	/**
	 * Get the html for social button icons as per required setting has enabled in backend
	 *
	 * @since 1.0.0
	 * @param int $user_id Id of the user.
	 * @return $content as the HTMl
	 */
	public function get_social_sharing_html( $user_id ) {
		$content  = '';
		$content .= '<div class="af_share_section">';

		if ( self::afp_check_is_enable( 'affliacte_social_facebook' ) ) {

			$content .= $this->get_facebook_html();
		}
		if ( self::afp_check_is_enable( 'affliacte_social_twitter' ) ) {

			$content .= $this->get_twiter_html();
		}
		if ( self::afp_check_is_enable( 'affliacte_social_email' ) ) {

			$content .= $this->get_mail_share_html();
		}
		if ( self::afp_check_is_enable( 'affliacte_social_email' ) ) {

			$content .= $this->get_whatsapp_share_link();
		}

		$content .= '</div>';

		return $content;
	}

	/**
	 * Function used for checking
	 *
	 * @name afp_check_is_enable
	 * @since 1.0.0
	 * @param string $option  Name of the option.
	 */
	public static function afp_check_is_enable( $option ) {

		$is_enable = get_option( $option, false );

		return ( ! empty( $is_enable && 'yes' == $is_enable ) ) ? true : false;
	}

	/**
	 * Function is to get the twitter text
	 *
	 * @name get_twiter_html
	 * @since 1.0.0
	 */
	public function get_twiter_html() {

		$af_twitter_button = '<div class="apf_main_class affliate_twitter_button"><a class="twitter-share-button" href="https://twitter.com/intent/tweet?text=' . $this->get_affiliate_link( get_current_user_ID() ) . '" target="_blank"><img src ="' . plugin_dir_url( __FILE__ ) . '/images/twitter.png">' . esc_html__( 'Tweet', ' affiliate-program-for-woocommerce' ) . '</a></div>';
		return $af_twitter_button;
	}

	/**
	 * Function is to get the twitter text
	 *
	 * @name get_facebook_html
	 * @since 1.0.0
	 */
	public function get_facebook_html() {
		wp_enqueue_script( $this->plugin_name . 'fb-js' );
		$afp_fb_button = '<div id="fb-root"></div>
			<div class="apf_main_class fb-share-button affliate_referral_program" data-href="' . $this->get_affiliate_link( get_current_user_ID() ) . '" data-layout="button_count" data-size="small" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=' . $this->get_affiliate_link( get_current_user_ID() ) . '">' . esc_html__( 'Share', 'affiliate-program-for-woocommerce' ) . '</a></div>';
		return $afp_fb_button;
	}

	/**
	 * Function is to get mail share html
	 *
	 * @name get_mail_share_html
	 * @since 1.0.0
	 */
	public function get_mail_share_html() {

		$af_mail_button = '<a class="apf_main_class mail_button affliate_class" href="mailto:enteryour@addresshere.com?subject=Click on this link &body=Check%20this%20out:%20' . $this->get_affiliate_link( get_current_user_ID() ) . '" rel="nofollow"><img src ="' . plugin_dir_url( __FILE__ ) . 'images/email.png"></a>';

		return $af_mail_button;
	}

	/**
	 * Function to get the whatsapp share link
	 *
	 * @name get_whatsapp_share_link
	 * @since 1.0.0
	 */
	public function get_whatsapp_share_link() {

		$af_whatspp_button = '<a class="apf_main_class whatsapp_button" href="https://api.whatsapp.com/send?text=%20' . urlencode( $this->get_affiliate_link( get_current_user_ID() ) ) . '" rel="nofollow" ><img src ="' . plugin_dir_url( __FILE__ ) . 'images/whatsapp.png"></a>';

		return $af_whatspp_button;
	}

	/**
	 * Function to get total earning
	 *
	 * @name apf_get_total_earning
	 * @since 1.0.0
	 * @param int $user_id  user id of the customer.
	 */
	public function apf_get_total_earning( $user_id ) {
		$total_earning = get_user_meta( $user_id, 'apf_total_earn', true );
		return ( ! empty( $total_earning ) ) ? $total_earning : 0;
	}

	/**
	 * Function is used for get total refunds
	 *
	 * @name apf_get_total_refunds
	 * @param int $user_id  user id of the customer.
	 * @since 1.0.0
	 */
	public function apf_get_total_refunds( $user_id ) {
		$apf_total_refunds = get_user_meta( $user_id, 'apf_total_refunds', true );
		return ( ! empty( $apf_total_refunds ) ) ? $apf_total_refunds : 0;
	}

	/**
	 * Function used for the get the total Current Balance
	 *
	 * @name apf_get_current_balance
	 * @since 1.0.0
	 * @param int $user_id  user id of the customer.
	 */
	public function apf_get_current_balance( $user_id ) {
		$apf_total_balance = get_user_meta( $user_id, 'apf_total_balance', true );
		return ( ! empty( $apf_total_balance ) ) ? $apf_total_balance : 0;
	}


	/**
	 * Set Affiliate Key into the cookies.
	 *
	 * @name apf_wp_loaded_set_affiliate_key
	 * @since 1.0.0
	 */
	public function apf_wp_loaded_set_affiliate_key() {
		if ( ! is_admin() && ! is_user_logged_in() ) {

			$apf_affiliate_expiry = 365;

			$affiliate_key = self::get_affliate_key();

			if ( isset( $_GET[ $affiliate_key ] ) && ! empty( $affiliate_key ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				$_apf_affilate_key = sanitize_text_field( wp_unslash( $_GET[ $affiliate_key ] ) );// phpcs:ignore WordPress.Security.NonceVerification
				$_apf_affilate_key = trim( $_apf_affilate_key );// phpcs:ignore WordPress.Security.NonceVerification

				if ( ! empty( $_apf_affilate_key ) ) {

					setcookie( 'apf_affiliate_cookie_set', $_apf_affilate_key, time() + ( 86400 * $apf_affiliate_expiry ), '/' );
				}
			}
		}
	}

	/**
	 * Function to set the affiliate user
	 *
	 * @name apf_create_affiliate_user
	 * @since 1.0.0
	 * @param int    $user_id  user id of the customer.
	 * @param array  $new_customer_data  Customer data.
	 * @param string $password_generated  Password.
	 */
	public function apf_create_affiliate_user( $user_id, $new_customer_data, $password_generated ) {

		// Get the saved cookie.
		$cookie_val = isset( $_COOKIE['apf_affiliate_cookie_set'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['apf_affiliate_cookie_set'] ) ) : '';

		if ( ! empty( $cookie_val ) ) {

			$args['meta_query'] = array(
				array(
					'key'     => 'affiliate_key',
					'value'   => trim( $cookie_val ),
					'compare' => '==',
				),
			);

			$args = apply_filters( 'apf_change_arguments', $args, $user_id );

			// query to fetch the refred user.

			$refere_data = get_users( $args );

			$refree_id = $refere_data[0]->data->ID;

			$_ref_user = get_user_by( 'ID', $refree_id );

			if ( ! empty( $refree_id ) ) {
				// Update the referred user.
				update_user_meta( $user_id, 'apf_affiliate_referred_by', $refree_id );

				// allow third party plugin to run their script.
				do_action( 'apf_referred_user', $user_id, $refree_id );
				$this->apf_destroy_cookie();
			}
		}

	}

	/**
	 * Destory Cookies When Customer Register Successful.
	 *
	 * @name mwb_wpr_destroy_cookie
	 * @since 1.0.0
	 */
	public function apf_destroy_cookie() {
		if ( ! empty( $_COOKIE['apf_affiliate_cookie_set'] ) ) {
			setcookie( 'apf_affiliate_cookie_set', '', time() - 3600, '/' );
		}
	}

	/**
	 * Function used for provide discount to affiliate user
	 *
	 * @name apf_provide_discount_to_affiliate
	 * @param int    $order_id  order id of the customer.
	 * @param string $old_status  old status of the order.
	 * @param int    $new_status  new status of the order.
	 * @param array  $order  WC_Order object.
	 * @since 1.0.0
	 */
	public function apf_provide_discount_to_affiliate( $order_id, $old_status, $new_status, $order ) {

		if ( $old_status != $new_status && 'completed' === $new_status ) {

			$_apf_is_awarded = true;

			$user_id = $order->get_user_id();

			// Check is user has been awarded or not.
			if ( self::apf_is_refree_user_awarded_or_not( $order ) ) {
				$_apf_is_awarded = false;
			}

			// Here We will increse limit each time.
			$this->apf_increse_number_of_orders( $user_id );

			$_apf_get_max_no_order = $this->apf_get_commission_limit();

			$_apf_get_used_no_order = $this->apf_get_commission_limit_used( $user_id );
			if ( $_apf_is_awarded && $_apf_get_max_no_order >= $_apf_get_used_no_order ) {

				$refree_id = get_user_meta( $user_id, 'apf_affiliate_referred_by', true );
				if ( empty( $refree_id ) ) {
					$refree_id = get_post_meta( $order_id, 'apf_referral_id', true );
				}
				// allow third party to run their script.

				$refree = get_user_by( 'ID', $refree_id );

				$_apf_commision_amt = $this->apf_get_affiliate_commission_amount( $order );
				// Update the user balance.
				$this->apf_set_user_total_balance( $refree_id, $_apf_commision_amt, 'update' );

				// Update the Total Earning.
				$this->apf_update_total_earning( $_apf_commision_amt, $refree_id );

				update_post_meta( $order_id, 'apf_user_awarded', $refree_id );

				// Update the commision amt in the order.

				update_post_meta( $order_id, 'apf_apf_commision_amt', $_apf_commision_amt );

				 // Update  reports.
				do_action( 'apf_asign_total_commission', $refree_id, $order_id, $_apf_commision_amt );
			}
		}
	}

	/**
	 * Function is used for calculate the discount
	 *
	 * @name apf_get_affiliate_commisiion_amount
	 * @since 1.0.0
	 * @param object $order  Objec of the WC_Order.
	 */
	public function apf_get_affiliate_commission_amount( $order ) {

		$_apf_commision_type = $this->apf_get_commision_type();
		$_apf_commision_amt  = $this->apf_get_commision_amt();
		$_apf_order_total    = $order->get_total();

		// Calculate the discount.
		if ( 'affliate_commission' === $_apf_commision_type ) {

			$_apf_discount_amt = $_apf_commision_amt;

		} elseif ( 'affliate_commision_percent' === $_apf_commision_type ) {

			$_apf_discount_amt = ( $_apf_commision_amt * $_apf_order_total ) / 100;
		}

		// Allow third party to update the value.
		$_apf_discount_amt = apply_filters( 'apf_get_discount_amt', $_apf_discount_amt, $order );

		return $_apf_discount_amt;
	}

	/**
	 * Get the commision max no
	 *
	 * @name apf_get_commission_limit
	 * @since 1.0.0
	 */
	public function apf_get_commission_limit() {
		$_apf_max_no_commision = get_option( 'restrict_no_of_order', false );
		return ! empty( $_apf_max_no_commision ) ? $_apf_max_no_commision : 1;
	}

	/**
	 * Get the commision type
	 *
	 * @name apf_get_commision_type
	 * @since 1.0.0
	 */
	public function apf_get_commision_type() {
		$_apf_commision_type = get_option( 'afflilate_commission_type', false );
		return ! empty( $_apf_commision_type ) ? $_apf_commision_type : 'affliate_commission';
	}

	/**
	 * Function to get the commision amount
	 *
	 * @name apf_get_commision_amt
	 * @since 1.0.0
	 */
	public function apf_get_commision_amt() {
		$_apf_commision_amt = get_option( 'allliate_commission_amt', false );
		return ! empty( $_apf_commision_amt ) ? $_apf_commision_amt : 1;
	}

	/**
	 * Function is used for get used limit of the order
	 *
	 * @name apf_get_commission_limit_used
	 * @since 1.0.0
	 * @param int $user_id  Id of the user.
	 */
	public function apf_get_commission_limit_used( $user_id ) {
		$_apf_used_limit = get_user_meta( $user_id, '_apf_used_limit', true );
		return ! empty( $_apf_used_limit ) ? $_apf_used_limit : 1;
	}

	/**
	 * Function to check is user is awarded or not
	 *
	 * @name apf_is_refree_user_awarded_or_not
	 * @since 1.0.0
	 * @param array $order WC_Order object.
	 */
	public static function apf_is_refree_user_awarded_or_not( $order ) {
		$_user_awarded = get_post_meta( $order->get_id(), 'apf_user_awarded', true );
		return ! empty( $_user_awarded ) ? true : false;
	}

	/**
	 * Function used to increse limit user
	 *
	 * @name apf_increse_number_of_orders
	 * @since 1.0.0
	 * @param int $user_id  Id of the user.
	 */
	public function apf_increse_number_of_orders( $user_id ) {
		$_apf_previous_limit = $this->apf_get_commission_limit_used( $user_id );
		if ( ! empty( $_apf_previous_limit ) ) {
			++$_apf_previous_limit;
		} else {
			$_apf_previous_limit = 1;
		}
		$_apf_previous_limit = apply_filters( 'apf_change_previous_limit', $_apf_previous_limit, $user_id );

		update_user_meta( $user_id, '_apf_used_limit', $_apf_previous_limit );
	}

	/**
	 * Update user total Balance
	 *
	 * @name apf_set_user_total_balance
	 * @since 1.0.0
	 * @param int             $user_id  Id of the user.
	 * @param int             $amt  Balance that will updated.
	 * @param string/optional $action  Action that will performed.
	 */
	public function apf_set_user_total_balance( $user_id, $amt, $action = 'update' ) {

		$_apf_previous_balance = $this->apf_get_current_balance( $user_id );
		if ( 'update' === $action ) {

			$_new_previous_balance = (float) $amt + (float) $_apf_previous_balance;

			if ( $_new_previous_balance > 0 ) {

				$_new_previous_balance = apply_filters( 'apf_update_total_balance', $_new_previous_balance, $_apf_previous_balance, $user_id );

				update_user_meta( $user_id, 'apf_total_balance', $_new_previous_balance );

				return true;
			}
		} elseif ( 'set' === $action ) {
			if ( $amt > 0 ) {
				$amt = apply_filters( 'apf_set_total_balance', $amt, $_apf_previous_balance, $user_id );
				update_user_meta( $user_id, 'apf_total_balance', $amt );
				return true;
			}
		}
		return false;
	}


	/**
	 * Function is used for update the total Earning
	 *
	 * @name apf_update_total_earning
	 * @since 1.0.0
	 * @param int $amt     Balance that will updated.
	 * @param int $user_id  of the user.
	 */
	public function apf_update_total_earning( $amt, $user_id ) {

		$_previous_total_earn = (float) $this->apf_get_total_earning( $user_id );
		$_new_total_earn      = $_previous_total_earn;
		if ( $amt > 0 ) {
			$_new_total_earn += (float) $amt;

			// Allow third party to update the total earn.
			$_new_total_earn = apply_filters( 'apf_total_earning', $_new_total_earn, $_previous_total_earn, $user_id );
			// Update user balance.
			update_user_meta( $user_id, 'apf_total_earn', $_new_total_earn );
			return true;
		}

		return false;
	}

	/**
	 * Function is used for update the total Refunds
	 *
	 * @name apf_update_total_earning
	 * @since 1.0.0
	 * @param int $amt  Amount of the user.
	 * @param int $user_id  user id of the usr.
	 */
	public function apf_update_total_refunds( $amt, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_ID();
		}
		$_previous_total_refund = (float) $this->apf_get_total_refunds( $user_id );

		$apf_total_refunds = get_user_meta( $user_id, 'apf_total_refunds', true );
		if ( $amt > 0 ) {
			$_new_total_refund += (float) $amt;

			// Allow third party to update the total earn.
			$_new_total_refund = apply_filters( 'apf_total_refunds', $_new_total_refund, $_previous_total_refund, $user_id );

			// Update user balance.
			update_user_meta( $user_id, 'apf_total_refunds', $_new_total_refund );
			return true;
		}

		return false;
	}

	/**
	 * Function will add new column on to the user table
	 *
	 * @name apf_modify_user_table_with_balance
	 * @since 1.0.0
	 * @param sting $column  column of the usr.
	 */
	public function apf_modify_user_table_with_balance( $column ) {
		$column['balance'] = esc_html__( 'Balance', 'affiliate-program-for-woocommerce' );
		return $column;
	}


	/**
	 * Show balance of the user on the user table.
	 *
	 * @name apf_add_value_user_table_with_balance
	 * @param int    $val               Value of the column.
	 * @param string $column_name    Name of the column.
	 * @param int    $user_id           Id of the WP_User.
	 * @since 1.0.0
	 */
	public function apf_add_value_user_table_with_balance( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'balance':
				return get_the_author_meta( 'apf_total_balance', $user_id );
			default:
		}
		return $val;
	}

	/**
	 * Add the custom field on the user profile.
	 *
	 * @name apf_add_custom_field_on_user_profile
	 * @param object $user  Object of the WP_User.
	 * @since 1.0.0
	 */
	public function apf_add_custom_field_on_user_profile( $user ) {
		?>
			<h3><?php esc_html_e( 'Extra profile information', 'affiliate-program-for-woocommerce' ); ?></h3>

			<table class="form-table">
				<tr>
					<th>
						<label for="apf_balance"><?php esc_html_e( 'Balance', 'affiliate-program-for-woocommerce' ); ?></label>
					</th>
					<td>
						<input type="text" name="apf_balance" id="apf_balance" value="<?php echo esc_attr( get_the_author_meta( 'apf_total_balance', $user->ID ) ); ?>" class="regular-text" /><br />
						<span class="description"><?php esc_html_e( 'Update user balance.', 'affiliate-program-for-woocommerce' ); ?></span>
					</td>
				</tr>
			</table>
		<?php
	}

	/**
	 * Update balance of the user
	 *
	 * @name apf_save_custom_field_on_user_profile
	 * @param int $user_id              Id of the WP_User.
	 * @since 1.0.0
	 */
	public function apf_save_custom_field_on_user_profile( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		check_admin_referer( 'update-user_' . $user_id );
		$previous = $this->apf_get_total_earning( $user_id );

		$balance = ! empty( $_POST['apf_balance'] ) ? sanitize_text_field( wp_unslash( $_POST['apf_balance'] ) ) : 0;
		if ( $previous == $balance ) {
			return;
		} elseif ( $previous > $balance ) {
			$_apf_commision_amt = $previous - $balance;
		} elseif ( $balance > $previous ) {
			$_apf_commision_amt = $balance - $previous;
		}

		$balance = apply_filters( 'apf_admin_set_total_balance', $balance, $user_id );

		update_user_meta( $user_id, 'apf_total_balance', $balance );
		update_user_meta( $user_id, 'apf_total_earn', $balance );

		$reports = get_user_meta( $user_id, 'reports', true );

		 $customer_reports = array(
			 'customer_name' => '',
			 'order_total'  => '',
			 'customer_event' => esc_html__( 'Admin update', 'affiliate-program-for-woocommerce' ),
			 'date'           => gmdate( 'dS M Y' ),
			 'earnings'       => $_apf_commision_amt,
			 'order_id'       => '',
			 'affiliate_user_id'        => '',
			 'total_balance'  => $this->apf_get_total_earning( $user_id ),
		 );
		 if ( empty( $reports ) ) {
			 $reports = array();
			 $reports[] = $customer_reports;
		 } else {
			 $reports[] = $customer_reports;
		 }
		 update_user_meta( $user_id, 'reports', $reports );

	}

	/**
	 * Register new endpoint for report
	 *
	 * @name apf_register_ednpoints
	 * @since 1.0.0
	 */
	public function apf_register_ednpoints() {
		 add_rewrite_endpoint( 'reports', EP_ROOT | EP_PAGES );
	}

	/**
	 * Show reports of the affilate
	 *
	 * @name apf_show_reports_for_affiliate
	 * @since 1.0.0
	 */
	public function apf_show_reports_for_affiliate() {
		require_once plugin_dir_path( __FILE__ ) . 'templates/affiliate-reports.php';
	}

	/**
	 * Update customer reports.
	 *
	 * @name apf_update_customer_reports
	 * @since 1.0.0
	 * @param int $refree_id              Id of the WP_User.
	 * @param int $order_id              Id of the WC_Order.
	 * @param int $_apf_commision_amt   amount of the commission.
	 */
	public function apf_update_customer_reports( $refree_id, $order_id, $_apf_commision_amt ) {
		$reports = get_user_meta( $refree_id, 'reports', true );
		$apf_total_referred = (int) get_user_meta( $refree_id, 'apf_total_referred', true );
		if ( empty( $apf_total_referred ) ) {
			$apf_total_referred = 1;
			update_user_meta( $refree_id, 'apf_total_referred', $apf_total_referred );
		} else {
			 ++$apf_total_referred;
			update_user_meta( $refree_id, 'apf_total_referred', $apf_total_referred );
		}
		$order = wc_get_order( $order_id );
		$user_ID         = $order->get_user_id();
		if ( 0 == $user_ID ) {
			$user_name = esc_html__( 'Guest User', 'affiliate-program-for-woocommerce' );
		} else {
			$user = get_user_by( 'ID', $user_ID );
			$user_name = $user->data->user_login;
		}
		$customer_reports = array(
			'customer_name' => $user_name,
			'order_total'  => $order->get_total(),
			'customer_event' => esc_html__( 'Earned commission on affiliate order', 'affiliate-program-for-woocommerce' ),
			'date'           => get_the_date( 'dS M Y', $order_id ),
			'earnings'       => $_apf_commision_amt,
			'order_id'       => $order_id,
			'affiliate_user_id'        => $user_ID,
			'total_balance'  => $this->apf_get_total_earning( $refree_id ),
		);
		if ( empty( $reports ) ) {
			$reports = array();
			$reports[] = $customer_reports;
		} else {
			$reports[] = $customer_reports;
		}
		update_user_meta( $refree_id, 'reports', $reports );
	}

	/**
	 * Update customer reports.
	 *
	 * @name apf_checkout_update_order_meta
	 * @since 1.0.0
	 * @param int   $order_id              Id of the WC_Order.
	 * @param array $data   array of the data.
	 */
	public function apf_checkout_update_order_meta( $order_id, $data ) {
		$_order = wc_get_order( $order_id );
		$cookie_val = isset( $_COOKIE['apf_affiliate_cookie_set'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['apf_affiliate_cookie_set'] ) ) : '';
		$user_id         = $_order->get_user_id();
		if ( ! empty( $cookie_val ) && '0' == $user_id ) {

			$args['meta_query'] = array(
				array(
					'key'     => 'affiliate_key',
					'value'   => trim( $cookie_val ),
					'compare' => '==',
				),
			);

			$args = apply_filters( 'apf_change_arguments', $args, $user_id );

			// query to fetch the refred user.

			$refere_data = get_users( $args );

			$refree_id = $refere_data[0]->data->ID;
			if ( ! empty( $refree_id ) ) {
				// Update the referred user.
				update_post_meta( $order_id, 'apf_referral_id', $refree_id );
				$this->apf_destroy_cookie();
			}
		}
	}
}
