<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Payments_Admin {
  /**
   * Constructor
   */
  public function __construct() {
    add_filter( 'woocommerce_get_sections_checkout', array( $this, 'register_section' ), 10, 1 );

    add_action( 'woocommerce_settings_checkout', array( $this, 'output' ) );
    
    add_action( 'woocommerce_settings_save_checkout', array( $this, 'save_ruleset' ), 10, 0 );
    add_action( 'woocommerce_settings_save_checkout', array( $this, 'save_settings' ), 10, 0 );

    // Add admin JS
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    
    // Add plugin links
    add_filter( 'plugin_action_links_' . WOO_CONDITIONAL_PAYMENTS_BASENAME, array( $this, 'add_plugin_links' ) );

    // Admin AJAX action for toggling ruleset activity
    add_action( 'wp_ajax_wcp_toggle_ruleset', array( $this, 'toggle_ruleset' ) );
	}
	
  /**
   * Add plugin links
   */
  public function add_plugin_links( $links ) {
    $url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_conditional_payments' );
		$link = '<a href="' . $url . '">' . __( 'Conditions', 'woo-conditional-payments' ) . '</a>';

		$links = array_merge( array( $link ), $links );
		
		if ( ! class_exists( 'Woo_Conditional_Payments_Pro' ) ) {
			$link = '<span style="font-weight:bold;"><a href="https://wooelements.com/products/conditional-payments" style="color:#46b450;" target="_blank">' . __( 'Go Pro', 'woo-conditional-payments' ) . '</a></span>';

			$links = array_merge( array( $link ), $links );
		}

    return $links;
  }

  /**
	 * Add admin JS
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'jquery-ui-autocomplete' );

		wp_enqueue_script(
			'woo-conditional-payments-admin-js',
			plugin_dir_url( __FILE__ ) . '../../admin/js/woo-conditional-payments.js',
			array( 'jquery', 'wp-util' ),
			WOO_CONDITIONAL_PAYMENTS_ASSETS_VERSION
		);

		wp_enqueue_style(
			'woo-conditional-payments-admin-css',
			plugin_dir_url( __FILE__ ) . '../../admin/css/woo-conditional-payments.css',
			array(),
			WOO_CONDITIONAL_PAYMENTS_ASSETS_VERSION
    );
    
		$ajax_url = add_query_arg( array(
			'action' => 'wcp_toggle_ruleset',
		), admin_url( 'admin-ajax.php' ) );

		wp_localize_script( 'woo-conditional-payments-admin-js', 'woo_conditional_payments', array(
			'ajax_url' => $ajax_url,
		) );
  }
  
  /**
   * Register section under "Payments" settings in WooCommerce
   */
  public function register_section( $sections ) {
    $sections['woo_conditional_payments'] = __( 'Conditions', 'woo-conditional-payments' );

    return $sections;
	}
	
  /**
   * Output conditions page
   */
  public function output() {
    global $current_section;
    global $hide_save_button;

    if ( 'woo_conditional_payments' === $current_section ) {
			if ( isset( $_REQUEST['ruleset_id'] ) ) {
        $hide_save_button = true;

        if ( $_REQUEST['ruleset_id'] === 'new' ) {
          $ruleset_id = false;
        } else {
          $ruleset_id = wc_clean( wp_unslash( $_REQUEST['ruleset_id'] ) );
        }

        if ( $ruleset_id && isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) {
					wp_delete_post( $ruleset_id, false );
					
					// Clear cache
					delete_transient( 'wcp_name_address_fields' );

          $url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_conditional_payments' );
          wp_safe_redirect( $url );
          exit;
        }

        $ruleset = new Woo_Conditional_Payments_Ruleset( $ruleset_id );

        include 'views/ruleset.html.php';
      } else {
        $hide_save_button = true;

        $health = $this->health_check();

        $rulesets = woo_conditional_payments_get_rulesets();
        
        include 'views/settings.html.php';
      }
    }
  }

	/**
	 * Save ruleset
	 */
	public function save_ruleset() {
		global $current_section;
    
    if ( 'woo_conditional_payments' === $current_section && isset( $_POST['ruleset_id'] ) ) {
      $post = false;
      if ( $_POST['ruleset_id'] ) {
        $post = get_post( $_POST['ruleset_id'] );

        if ( ! $post && 'wcp_ruleset' !== get_post_type( $post ) ) {
          $post = false;
        }
      }

      if ( ! $post ) {
        $post_id = wp_insert_post( array(
          'post_type' => 'wcp_ruleset',
          'post_title' => wp_strip_all_tags( $_POST['ruleset_name'] ),
          'post_status' => 'publish',
        ) );

        $post = get_post( $post_id );
      } else {
        $post->post_title = wp_strip_all_tags( $_POST['ruleset_name'] );

        wp_update_post( $post, false );
      }

      $operator = isset( $_POST['wcp_operator'] ) ? $_POST['wcp_operator'] : 'and';
      update_post_meta( $post->ID, '_wcp_operator', $operator );

      $conditions = isset( $_POST['wcp_conditions'] ) ? $_POST['wcp_conditions'] : array();
      update_post_meta( $post->ID, '_wcp_conditions', array_values( (array) $conditions ) );

			$actions = isset( $_POST['wcp_actions'] ) ? $_POST['wcp_actions'] : array();
			update_post_meta( $post->ID, '_wcp_actions', array_values( (array) $actions ) );
      
			$enabled = ( isset( $_POST['ruleset_enabled'] ) && $_POST['ruleset_enabled'] ) ? 'yes' : 'no';
			update_post_meta( $post->ID, '_wcp_enabled', $enabled );
			
			// Clear cache
			delete_transient( 'wcp_name_address_fields' );

      $url = add_query_arg( array(
        'ruleset_id' => $post->ID,
      ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_conditional_payments' ) );
      wp_safe_redirect( $url );
      exit;
    }
  }

  /**
   * Save general settings
   */
  public function save_settings() {
    global $current_section;
    
    if ( 'woo_conditional_payments' === $current_section && isset( $_POST['wcp_settings'] ) ) {
      update_option( 'wcp_disable_all', ( isset( $_POST['wcp_disable_all'] ) && $_POST['wcp_disable_all'] ) );
    }
  }
  
  /**
   * Toggle reulset
   */
  public function toggle_ruleset() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
      http_response_code( 403 );
      die( 'Permission denied' );
    }

    $ruleset_id = $_POST['id'];

    $post = get_post( $ruleset_id );

    if ( $post && get_post_type( $post ) === 'wcp_ruleset' ) {
      $enabled = get_post_meta( $post->ID, '_wcp_enabled', true ) === 'yes';
      $new_status = $enabled ? 'no' : 'yes';
      update_post_meta( $post->ID, '_wcp_enabled', $new_status );

      echo json_encode( array(
        'enabled' => ( get_post_meta( $post->ID, '_wcp_enabled', true ) === 'yes' ),
      ) );
      
      die;
    }

    http_response_code(422);
    die;
  }


  /**
   * Health check
   */
  private function health_check() {
    return array(
      'enables' => $this->health_check_enables(),
      'disables' => $this->health_check_disables(),
    );
  }

  /**
   * Check if there are disabled payment methods in the rulesets
   * 
   * Conditional Payments can only process payments methods which are enabled
   */
  private function health_check_disables() {
    // Get all rulesets
    $rulesets = woo_conditional_payments_get_rulesets( true );

    $payment_method_actions = array(
      'enable_payment_methods', 'disable_payment_methods',
      'add_fee'
    );

    $disables = array();
    foreach ( $rulesets as $ruleset ) {
      foreach ( $ruleset->get_actions() as $action ) {
        if ( in_array( $action['type'], $payment_method_actions, true ) && isset( $action['payment_method_ids'] ) && is_array( $action['payment_method_ids'] ) ) {
          foreach ( $action['payment_method_ids'] as $instance_id ) {
            $gateway = woo_conditional_payments_get_payment_method( $instance_id );

            if ( $gateway && is_object( $gateway ) && isset( $gateway->enabled ) && $gateway->enabled !== 'yes' ) {
              $disables[] = array(
                'gateway' => $gateway,
                'ruleset' => $ruleset,
                'action' => $action,
              );
            }
          }
        }
      }
    }

    return $disables;
  }

  /**
   * Check for multiple "Enable payment methods" for the same payment method
   */
  private function health_check_enables() {
    // Get all rulesets
    $rulesets = woo_conditional_payments_get_rulesets( true );

    // Check if there are overlapping "Enable payment methods"
    $enables = array();
    foreach ( $rulesets as $ruleset ) {
      foreach ( $ruleset->get_actions() as $action ) {
        if ( $action['type'] === 'enable_payment_methods' && isset( $action['payment_method_ids'] ) && is_array( $action['payment_method_ids'] ) ) {
          foreach ( $action['payment_method_ids'] as $id ) {
            if ( ! isset( $enables[$id] ) ) {
              $enables[$id] = array();
            }

            $enables[$id][] = $ruleset->get_id();
          }
        }
      }
    }

    // Filter out if there is only one "Enable payment methods" for a payment method
    $enables = array_filter( $enables, function( $ruleset_ids ) {
      return count( $ruleset_ids ) > 1;
    } );

    return $enables;
  }
}
