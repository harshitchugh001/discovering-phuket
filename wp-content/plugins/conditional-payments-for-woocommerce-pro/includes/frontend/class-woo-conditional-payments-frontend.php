<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Payments_Frontend {
  private $passed_rule_ids = array();

  /**
   * Constructor
   */
  public function __construct() {
		// Load frontend styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 0 );

    if ( ! get_option( 'wcp_disable_all', false ) ) {
      // Filter payment methods
      add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_payment_methods' ), 10, 1 );
      
      // Add payment method fees
      add_action( 'woocommerce_cart_calculate_fees', array( $this, 'payment_method_fees' ), 10, 0 );
      
      // Store all post data into the session so data can be used in filters
      add_action( 'woocommerce_checkout_update_order_review', array( $this, 'store_customer_details' ), 10, 1 );

      // Set custom no payment methods available message
      add_filter( 'woocommerce_no_available_payment_methods_message', [ $this, 'set_no_payment_methods_msg' ], 10, 1 );
    }
  }

  /**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
    $version = '0.0.0';
    if ( defined( 'CONDITIONAL_PAYMENTS_FOR_WOO_PRO_VERSION' ) ) {
      $version = CONDITIONAL_PAYMENTS_FOR_WOO_PRO_VERSION;
    } else if ( defined( 'CONDITIONAL_PAYMENTS_FOR_WOO_VERSION' ) ) {
      $version = CONDITIONAL_PAYMENTS_FOR_WOO_VERSION;
    }

		wp_enqueue_script(
			'woo-conditional-payments-js',
			plugin_dir_url( __FILE__ ) . '../../frontend/js/woo-conditional-payments.js',
			array( 'jquery' ),
			$version
    );
    
		wp_localize_script( 'woo-conditional-payments-js', 'conditional_payments_settings', array(
			'name_address_fields' => $this->name_address_fields(),
		) );
  }
  
  /**
   * Get fields which require manual trigger for checkout update
   * 
   * By default changing first name, last name, company and certain other fields
   * do not trigger checkout update. Thus we need to trigger update manually if we have
   * conditions for these fields.
   * 
   * Triggering will be done in JS. However, we check here if we have conditions for these
   * fields. If we dont have, we dont want to trigger update as that would be unnecessary.
   */
  public function name_address_fields() {
    if ( false === ( $found_fields = get_transient( 'wcp_name_address_fields' ) ) ) {
      $rulesets = woo_conditional_payments_get_rulesets( true );

      $fields = array(
        'billing_first_name', 'billing_last_name', 'billing_company',
        'shipping_first_name', 'shipping_last_name', 'shipping_company',
        'billing_email', 'billing_phone',
      );
  
      $found_fields = array();
      foreach ( $rulesets as $ruleset ) {
        foreach ( $ruleset->get_conditions() as $condition ) {
          if ( in_array( $condition['type'], $fields ) ) {
            $found_fields[$condition['type']] = true;
          }

          // Special handling for "previous orders - match guests by email"
          if ( $condition['type'] === 'orders' && isset( $condition['orders_match_guests_by_email'] ) && $condition['orders_match_guests_by_email'] ) {
            $found_fields['billing_email'] = true;
          }

          // Special handling for Kybernaut IČO DIČ
          if ( strpos( $condition['type'], 'woolab_' ) !== false ) {
            $found_fields['billing_ic'] = true;
            $found_fields['billing_dic'] = true;
            $found_fields['billing_dic_dph'] = true;
          }
        }
      }

      $found_fields = array_keys( $found_fields );
      
      set_transient( 'wcp_name_address_fields', $found_fields, 60 * MINUTE_IN_SECONDS );
    }

    return $found_fields;
  }

  /**
	 * Store customer details to the session for being used in filters
	 */
	public function store_customer_details( $post_data ) {
		$data = array();
		parse_str( $post_data, $data );

		$attrs = array(
			'billing_first_name', 'billing_last_name', 'billing_company',
      'shipping_first_name', 'shipping_last_name', 'shipping_company',
      'billing_email', 'billing_phone'
		);

		$same_addr = FALSE;
		if ( ! isset( $data['ship_to_different_address'] ) || $data['ship_to_different_address'] != '1' ) {
			$same_addr = TRUE;
			$attrs = array(
				'billing_first_name', 'billing_last_name', 'billing_company', 'billing_email', 'billing_phone',
			);
		}

		foreach ( $attrs as $attr ) {
			WC()->customer->set_props( array(
				$attr => isset( $data[$attr] ) ? wp_unslash( $data[$attr] ) : null,
			) );

			if ( $same_addr ) {
				$attr2 = str_replace( 'billing', 'shipping', $attr );
				WC()->customer->set_props( array(
					$attr2 => isset( $data[$attr] ) ? wp_unslash( $data[$attr] ) : null,
				) );
			}
		}
  }
  
  /**
   * Add payment method fees
   */
  public function payment_method_fees() {
    if ( is_admin() ) {
      return;
    }

    $rulesets = woo_conditional_payments_get_rulesets( true );
    $selected_payment_method = WC()->session->get( 'chosen_payment_method' );

    foreach ( $rulesets as $ruleset ) {
      $passes = $ruleset->validate();

      if ( $passes ) {
        foreach ( $ruleset->get_actions() as $action ) {
          if ( $action['type'] === 'add_fee' ) {
            if ( in_array( $selected_payment_method, (array) $action['payment_method_ids'] ) ) {
              $title = $action['fee_title'] ? $action['fee_title'] : __( 'Payment method fee', 'woo-conditional-payments' );
              $amount = $action['fee_amount'];
              $tax = $action['fee_tax'];
              $mode = isset( $action['fee_mode'] ) ? $action['fee_mode'] : 'fixed';

              if ( $mode === 'pct' ) {
                $base = apply_filters( 'woo_conditional_payments_fee_base_amount', WC()->cart->get_displayed_subtotal(), $ruleset, $action );
                $amount = $base * ( floatval( $action['fee_amount'] ) / 100 );
              }
              
              $amount = apply_filters( 'woo_conditional_payments_fee_amount', $amount, $ruleset, $action );

              if ( $tax !== '_none' ) {
                WC()->cart->add_fee( $title, floatval( $amount ), true, $tax );  
              } else {
                WC()->cart->add_fee( $title, floatval( $amount ), false );  
              }
            }
          }
        }
      }
    }
  }

  /**
   * Get passed rules from session
   */
  private function get_passed_rules() {
    $passed_rule_ids = $this->passed_rule_ids;
    $passed_rules = array();

    if ( ! empty( $passed_rule_ids ) ) {
      $rulesets = woo_conditional_payments_get_rulesets( true );

      foreach ( $rulesets as $ruleset ) {
        if ( in_array( $ruleset->get_id(), $passed_rule_ids, true ) ) {
          $passed_rules[] = $ruleset;
        }
      }
    }

    return $passed_rules;
  }

  /**
   * Set custom no payment methods available message
   */
  public function set_no_payment_methods_msg( $orig_msg ) {
    $msgs = [];
    $i = 1;

    foreach ( $this->get_passed_rules() as $ruleset ) {
      foreach ( $ruleset->get_actions() as $action_index => $action ) {
        if ( $action['type'] === 'set_no_payments_methods_msg' && ! empty( $action['error_msg'] ) ) {
          $msgs[] = sprintf( '<div class="conditional-payments-custom-error-msg i-%d">%s</div>', $i, $action['error_msg'] );
          $i++;
        }
      }
    }

    if ( ! empty( $msgs ) ) {
      return implode( '', $msgs );
    }

    return $orig_msg;
  }
  
  /**
   * Filter payments methods
   */
  public function filter_payment_methods( $gateways ) {
    $rulesets = woo_conditional_payments_get_rulesets( true );
    $this->passed_rule_ids = array();

    $disable_keys = array();
    $enable_keys = array();

    foreach ( $rulesets as $ruleset ) {
      $passes = $ruleset->validate();

      if ( $passes ) {
        $this->passed_rule_ids[] = $ruleset->get_id();
      }

      foreach ( $ruleset->get_actions() as $action ) {
        if ( $action['type'] === 'disable_payment_methods' ) {
          if ( $passes ) {
            foreach ( $gateways as $key => $gateway ) {
              if ( in_array( $key, (array) $action['payment_method_ids'] ) ) {
                $disable_keys[$key] = true;
                unset( $enable_keys[$key] );
              }
            }
          }
        }

        if ( $action['type'] === 'enable_payment_methods' ) {
          foreach ( $gateways as $key => $gateway ) {
            if ( in_array( $key, (array) $action['payment_method_ids'] ) ) {
              if ( $passes ) {
                $enable_keys[$key] = true;
                unset( $disable_keys[$key] );
              } else {
                $disable_keys[$key] = true;
                unset( $enable_keys[$key] );
              }
            }
          }
        }
      }
    }

    foreach ( $gateways as $key => $gateway ) {
      if ( isset( $disable_keys[$key] ) && ! isset( $enable_keys[$key] ) ) {
        unset( $gateways[$key] );
      }
    }

    return $gateways;
  }
}
