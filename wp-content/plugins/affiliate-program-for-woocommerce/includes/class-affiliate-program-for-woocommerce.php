<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://eplugins.in/
 * @since      1.0.0
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/includes
 */
class Affiliate_Program_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @var      Affilate_Program_For_Woocoomerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AFFILIATE_PROGRAM_FOR_WOOCOOMERCE_VERSION' ) ) {
			$this->version = AFFILIATE_PROGRAM_FOR_WOOCOOMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'affiliate-program-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-affiliate-program-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-affiliate-program-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-affiliate-program-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-affiliate-program-for-woocommerce-public.php';

		$this->loader = new Affiliate_Program_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Affilate_Program_For_Woocoomerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new Affiliate_Program_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Affiliate_Program_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->id = 'affiliate-program_setting';
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// Add a tab for the Affiliate Program.
		$this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_admin, 'affiliate_woocommerce_settings_tabs_option', 50 );
		$this->loader->add_action( 'woocommerce_settings_tabs_' . $this->id, $plugin_admin, 'affiliate_program_settings_tab' );
		$this->loader->add_action( 'woocommerce_settings_save_' . $this->id, $plugin_admin, 'affiliate_program_setting_save' );
		$this->loader->add_action( 'woocommerce_sections_' . $this->id, $plugin_admin, 'affiliate_output_sections' );
		$this->loader->add_filter( 'woocommerce_admin_reports', $plugin_admin, 'apf_add_admin_reports' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_public_hooks() {

		$plugin_public = new Affiliate_Program_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		if ( self::is_affiliate_enable() ) { // check is plugin is enable or not.
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			$this->loader->add_action( 'woocommerce_account_dashboard', $plugin_public, 'get_affiliate_section' );
			$this->loader->add_action( 'wp_loaded', $plugin_public, 'apf_wp_loaded_set_affiliate_key' );
			$this->loader->add_action( 'woocommerce_created_customer', $plugin_public, 'apf_create_affiliate_user', 10, 3 );
			$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_public, 'apf_provide_discount_to_affiliate', 10, 4 );
			$this->loader->add_filter( 'manage_users_columns', $plugin_public, 'apf_modify_user_table_with_balance' );
			$this->loader->add_filter( 'manage_users_custom_column', $plugin_public, 'apf_add_value_user_table_with_balance', 10, 3 );
			$this->loader->add_filter( 'show_user_profile', $plugin_public, 'apf_add_custom_field_on_user_profile' );
			$this->loader->add_filter( 'edit_user_profile', $plugin_public, 'apf_add_custom_field_on_user_profile' );
			$this->loader->add_filter( 'personal_options_update', $plugin_public, 'apf_save_custom_field_on_user_profile' );
			$this->loader->add_filter( 'edit_user_profile_update', $plugin_public, 'apf_save_custom_field_on_user_profile' );
			$this->loader->add_action( 'init', $plugin_public, 'apf_register_ednpoints' );
			$this->loader->add_action( 'woocommerce_account_reports_endpoint', $plugin_public, 'apf_show_reports_for_affiliate' );
			$this->loader->add_action( 'apf_asign_total_commission', $plugin_public, 'apf_update_customer_reports', 10, 3 );
			$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'apf_checkout_update_order_meta', 10, 2 );

		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @name run
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Affilate_Program_For_Woocoomerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Check plugin is enable to work or not
	 *
	 * @name is_affliate_enable
	 * @since 1.0.0
	 */
	public static function is_affiliate_enable() {
		// fetch the value from the database.
		$affliate_enable    = get_option( 'affiliate_plugin_enable', false );
		$is_affliate_enable = false;

		if ( ! empty( $affliate_enable ) && 'yes' == $affliate_enable ) { // chek condtion is enable.
			$is_affliate_enable = true;
		}
		return $is_affliate_enable;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
