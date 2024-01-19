<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://eplugins.in/
 * @since      1.0.0
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/admin
 */
class Affiliate_Program_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin-facing side of the site.
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
		 wp_enqueue_style( 'datatables_css', plugin_dir_url( __FILE__ ) . 'css/affiliate-program-for-woocommerce-admin-datatable.css', array(), $this->version, 'all' );
		 wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/affiliate-program-for-woocommerce-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin-facing side of the site.
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
		wp_enqueue_script( 'bootstrap_js', plugin_dir_url( __FILE__ ) . 'js/affiliate-program-for-woocommerce-admin-datatables.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/affiliate-program-for-woocommerce-admin.js', array( 'jquery', 'bootstrap_js' ), $this->version, false );

	}

	/**
	 * This function will display the settings.
	 *
	 * @since    1.0.0
	 * @name affilate_program_settings_tab
	 */
	public function affiliate_program_settings_tab() {
		global $current_section;

		woocommerce_admin_fields( self::affiliate_get_settings( $current_section ) );
	}

	/**
	 * Display the html of each sections using Setting API.
	 *
	 * @name afflilate_get_settings
	 * @param  array $current_section array of the display sections.
	 * @since    1.0.0
	 */
	public static function affiliate_get_settings( $current_section ) {

		$settings = array();
		if ( '' === $current_section ) {
			$settings = array(

				array(
					'title' => esc_html__( 'General Setting', 'affiliate-program-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'general_options',
				),

				array(
					'title'   => esc_html__( 'Enable/Disable ', 'affiliate-program-for-woocommerce' ),
					'desc'    => esc_html__( 'Enable affiliate program ', 'affiliate-program-for-woocommerce' ),
					'default' => 'no',
					'type'    => 'checkbox',
					'id'      => 'affiliate_plugin_enable',
				),
				array(
					'title'             => esc_html__( 'Affiliate Key Length', 'affiliate-program-for-woocommerce' ),
					'default'           => 7,
					'type'              => 'number',
					'custom_attributes' => array(
						'min' => '7',
						'max' => '10',
					),
					'id'                => 'affilate_key_length',
					'class'             => 'affilate_input_val',
					'desc_tip'          => __( 'Set the length for affliate key. The minimum & maximum length a affliate key can have are 7 & 10.', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'title'    => esc_html__( 'Affiliate Key Name', 'affiliate-program-for-woocommerce' ),
					'default'  => 'affiliate_code',
					'type'     => 'text',
					'id'       => 'affilate_key_name',
					'class'    => 'affilate_input_val',
					'desc_tip' => __( 'Set the name for affliate key.', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'title'    => esc_html__( 'Text above the affiliate link', 'affiliate-program-for-woocommerce' ),
					'default'  => __( 'Refer your friends and you’ll earn commission on their purchases', 'affiliate-program-for-woocommerce' ),
					'type'     => 'textarea',
					'id'       => 'affilate_bef_text',
					'class'    => 'affilate_input_val',
					'desc_tip' => __( 'Set the text for affliate link.', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'title'             => esc_html__( 'Max. no. for commision on orders', 'affiliate-program-for-woocommerce' ),
					'default'           => 1,
					'type'              => 'number',
					'custom_attributes' => array( 'min' => '1' ),
					'id'                => 'restrict_no_of_order',
					'class'             => 'affilate_input_val',
					'desc_tip'          => __( 'Set the Maximum number of orders to give the commision  affliate orders.', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'title'    => esc_html__( 'Commission type', 'affiliate-program-for-woocommerce' ),
					'default'  => 1,
					'type'     => 'select',
					'id'       => 'afflilate_commission_type',
					'class'    => 'affilate_input_val',
					'options'  => array(
						'affliate_commission'        => esc_html__( 'Fixed', 'affiliate-program-for-woocommerce' ),
						'affliate_commision_percent' => esc_html__( 'Percentage', 'affiliate-program-for-woocommerce' ),
					),
					'desc_tip' => esc_html__( 'Commission will be calculate on the order total', 'affiliate-program-for-woocommerce' ),
					'desc'     => esc_html__( 'If you select the “Percentage” option then "Commission amount" will be calculated on the order total purchased by referred customers otherwise fixed amount commission will be allocated. ', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'title'             => esc_html__( 'Commission amount', 'affiliate-program-for-woocommerce' ),
					'default'           => 1,
					'type'              => 'number',
					'custom_attributes' => array( 'min' => '1' ),
					'id'                => 'allliate_commission_amt',
					'class'             => 'affilate_input_val',
					'desc_tip'          => esc_html__( 'Enter the commission value you want to give your customers, who have sent other users on your site.', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'title'             => esc_html__( 'Select roles', 'affiliate-program-for-woocommerce' ),
					'type'              => 'multiselect',
					'id'                => 'affiliate_select_role',
					'class'             => 'wc-enhanced-select',
					'desc_tip'          => esc_html__( 'Select user roles for which you wanted to allow affliate otherwise leave blank for all user roles.', 'affiliate-program-for-woocommerce' ),
					'options'           => self::get_affiliate_user_roles(),
					'desc'     => esc_html__( 'All the user with above roles will be converted into affiliate users. Leave it blank to allow affiliate program for all users.', 'affiliate-program-for-woocommerce' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'general_options',
				),
			);
		}
		if ( 'sharing-settings' === $current_section ) {
			$settings = array(
				array(
					'title' => esc_html__( 'Social sharing', 'affiliate-program-for-woocommerce' ),
					'type'  => 'title',
				),

				array(
					'title'   => esc_html__( 'Facebook', 'affiliate-program-for-woocommerce' ),
					'default' => 'no',
					'type'    => 'checkbox',
					'id'      => 'affliacte_social_facebook',
				),

				array(
					'title'   => esc_html__( 'Twitter', 'affiliate-program-for-woocommerce' ),
					'default' => 'no',
					'type'    => 'checkbox',
					'id'      => 'affliacte_social_twitter',
				),

				array(
					'title'   => esc_html__( 'Email', 'affiliate-program-for-woocommerce' ),
					'default' => 'no',
					'type'    => 'checkbox',
					'id'      => 'affliacte_social_email',
				),
				array(
					'title'   => esc_html__( 'WhatsApp', 'affiliate-program-for-woocommerce' ),
					'default' => 'no',
					'type'    => 'checkbox',
					'id'      => 'affliacte_social_whatsapp',
				),
				array(
					'type' => 'sectionend',
				),
			);
		}
		if ( 'reports' == $current_section ) {
			wp_redirect( admin_url( 'admin.php?page=wc-reports&tab=apf_admin_reports' ) );
			exit;
		}

		return apply_filters( 'affiliate_get_settings', $settings );
	}

	/** Get all roles
	 *
	 * @name get_affiliate_user_roles
	 * @since 1.0.0
	 */
	public static function get_affiliate_user_roles() {
		global $wp_roles;
		$roles = $wp_roles->get_names();
		return $roles;
	}

	/**
	 * Get all store products
	 *
	 * @name get_products
	 * @since    1.0.0
	 */
	public static function get_products() {
		$all_products = array();
		// arguments for all products.

		$args = array(
			'limit' => -1,
		);

		// Allow third party plugin to customize the arguments.
		$args = apply_filters( 'affliate_product_arguments', $args );

		$products = wc_get_products( $args );

		if ( ! empty( $products ) && is_array( $products ) ) {

			foreach ( $products as $product ) {

				if ( is_object( $product ) ) { // check product is object.

					$all_products[ $product->get_id() ] = $product->get_name();
				}
			}
		}
		return $all_products;
	}

	/**
	 * Function is used for adding the affliate settings tab.
	 *
	 * @param array $settings_tabs  All settings tabs.
	 * @since    1.0.0
	 * @return array of settings.
	 */
	public function affiliate_woocommerce_settings_tabs_option( $settings_tabs ) {
		$settings_tabs['affiliate-program_setting'] = esc_html__( 'Affiliate', 'affiliate-program-for-woocommerce' );

		return $settings_tabs;
	}

	/**
	 * Save the data using Setting API
	 *
	 * @since    1.0.0
	 * @name affiliate_program_setting_save
	 */
	public function affiliate_program_setting_save() {

		global $current_section;
		$settings = self::affiliate_get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Print the sections
	 *
	 * @name affiliate_output_sections
	 * @since    1.0.0
	 */
	public function affiliate_output_sections() {

		global $current_section;
		$sections = self::affiliate_get_sections();

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=affiliate-program_setting&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . esc_attr( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';// phpcs-ignore.
		}
		echo '</ul><br class="clear">';
	}

	/**
	 * Set the array for each sections
	 *
	 * @name affiliate_get_sections
	 * @since    1.0.0
	 */
	public static function affiliate_get_sections() {

		$sections = array(
			''                 => __( 'General settings', 'affiliate-program-for-woocommerce' ),
			'sharing-settings' => __( 'Sharing settings', 'affiliate-program-for-woocommerce' ),
			'reports'          => __( 'Affiliate reports', 'affiliate-program-for-woocommerce' ),
		);
		return apply_filters( 'affiliate_program_get_sections', $sections );
	}

	/**
	 * This function will add new tabs in reorts
	 *
	 * @name apf_add_admin_reports
	 * @param array $reports  All reporting tabs.
	 * @since 1.0.0
	 */
	public function apf_add_admin_reports( $reports ) {

		$reports['apf_admin_reports'] = array(
			'title'   => esc_html__( 'Affiliate reports', 'affiliate-program-for-woocommerce' ),
			'reports' => array(
				'apf_reports' => array(
					'title'       => esc_html__( 'Affiliate reports', 'affiliate-program-for-woocommerce' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'apf_get_report' ),
				),
			),
		);
		return $reports;
	}

	/**
	 * Function is used for display the affiliate program reports.
	 *
	 * @since 1.0.0
	 * @name apf_get_report
	 */
	public function apf_get_report() {
		require_once plugin_dir_path( __FILE__ ) . 'class-affiliate-report.php';
	}

}
