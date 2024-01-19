<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://eplugins.in/
 * @since             1.0.0
 * @package           Affiliate_Program_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Affiliate Program For WooCommerce
 * Plugin URI:        https://eplugins.in/
 * Description:       This extension will add the affiliate program in your store.
 * Version:           1.2.1
 * Author:            E Plugins
 * Author URI:        https://eplugins.in/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 3.0.0
 * Tested up to:      5.5.3
 * WC tested up to:   4.7.1
 * Text Domain:       affiliate-program-for-woocommerce
 * Domain Path:       /languages
 * Woo: 6036920:55caa761b0dde6bf33571a7cbe7c1d23
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$_plugin_activated = false;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	$_plugin_activated = true;
}

if ( $_plugin_activated ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	/**
	 * The code that runs during plugin activation.
	 *
	 * @name activate_affiliate_program_for_woocommerce
	 * @since 1.0.0
	 */
	function activate_affiliate_program_for_woocommerce() {
		// Create transient data.
		set_transient( 'affiliate_program_activate_notice', true, 5 );
	}

	register_activation_hook( __FILE__, 'activate_affiliate_program_for_woocommerce' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-affiliate-program-for-woocommerce.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_affiliate_program_for_woocommerce() {

		$plugin = new Affiliate_Program_For_Woocommerce();
		$plugin->run();

	}
	run_affiliate_program_for_woocommerce();


	/**
	 * Show notice when plugin is activated.
	 *
	 * @name affilate_program_for_woocommerce_show_notice
	 * @since 1.0.0
	 */
	function affiliate_program_for_woocommerce_show_notice() {
		if ( get_transient( 'affiliate_program_activate_notice' ) ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p><strong><?php esc_html_e( 'Welcome to Affiliate Program For Woocommerce –', 'affiliate-program-for-woocommerce' ); ?></strong><?php esc_html_e( ' To get started, enable the plugin on the', 'affiliate-program-for-woocommerce' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=affiliate-program_setting' ) ); ?>"><?php esc_html_e( 'settings page', 'affiliate-program-for-woocommerce' ); ?></a>.</p>
				<p class="affliate_submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=affiliate-program_setting' ) ); ?>" class="button-primary"><?php esc_html_e( 'Go to Settings', 'affiliate-program-for-woocommerce' ); ?></a></p>
			</div>
			<?php
			delete_transient( 'affiliate_program_activate_notice' );
		}
	}

	// Add admin notice only on plugin activation.
	add_action( 'admin_notices', 'affiliate_program_for_woocommerce_show_notice' );

	/**
	 * Create Settings for the Plugin
	 *
	 * @name affiliate_program_for_woocommerce_settings_link
	 * @since 1.0.0
	 * @param array $settings_links  array of the link.
	 */
	function affiliate_program_for_woocommerce_settings_link( $settings_links ) {

		$settings_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=affiliate-program_setting' ) . '">' . esc_html__( 'Settings', 'affiliate-program-for-woocommerce' ) . '</a>';
		return $settings_links;
	}

	// Add settings link on plugin page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'affiliate_program_for_woocommerce_settings_link' );

} else {

	/**
	 * Deactivate this plugin.
	 *
	 * @name affilate_program_for_woocommerce_activation_failure
	 * @since 1.0.0
	 */
	function affiliate_program_for_woocommerce_activation_failure() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}


	// WooCommerce is not active so deactivate this plugin.
	add_action( 'admin_init', 'affiliate_program_for_woocommerce_activation_failure' );

	/**
	 * Show error notice on the failure of woocoomerce install.
	 *
	 * @name affiliate_program_for_woocommerce_admin_notice
	 * @since 1.0.0
	 */
	function affiliate_program_for_woocommerce_admin_notice() {

		// to hide Plugin activated notice.
		unset( $_GET['activate'] );

		?>

		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'WooCommerce is not activated, Please activate WooCommerce first to activate Affilate Program For Woocommerce.', 'affiliate-program-for-woocommerce' ); ?></p>
		</div>

		<?php
	}

	// Add admin error notice.
	add_action( 'admin_notices', 'affiliate_program_for_woocommerce_admin_notice' );
}

