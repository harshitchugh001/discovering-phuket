<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Payments_Pro_Functions {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Filters
		add_filter( 'woo_conditional_payments_filters', array( $this, 'register_pro_filters' ), 10, 1 );

		// Actions
		add_filter( 'woo_conditional_payments_actions', array( $this, 'register_pro_actions' ), 10, 1 );

		// Store custom fields provided by 3rd party plugins (e.g. Aelia EU VAT Assistant)
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'store_custom_fields' ], 10, 1 );
	}

	/**
	 * Register Pro actions
	 */
	public function register_pro_actions( $actions ) {
		$actions['add_fee'] = array(
			'title' => __( 'Add payment method fee', 'woo-conditional-payments' ),
		);

		$actions['set_no_payments_methods_msg'] = array(
			'title' => __( 'Set no payment methods available message', 'woo-conditional-payments' ),
		);

		return $actions;
	}

	/**
	 * Register Pro filters
	 */
	public function register_pro_filters( $filters ) {
		$general_filters = array(
			'items' => array(
				'title' => __( 'Number of Items', 'woo-conditional-payments' ),
				'operators' => array( 'gt', 'gte', 'lt', 'lte' ),
			),
			'product_cats' => array(
				'title' => __( 'Product Categories', 'woo-conditional-payments' ),
				'operators' => array( 'in', 'exclusive', 'notin' )
			),
			'product_types' => array(
				'title' => __( 'Product Types', 'woo-conditional-payments' ),
				'operators' => array( 'in', 'exclusive', 'notin' )
			),
			'shipping_class' => array(
				'title' => __( 'Shipping Class', 'woo-conditional-payments' ),
				'operators' => array( 'in', 'exclusive', 'notin' )
			),
			'coupon' => array(
				'title' => __( 'Coupon', 'woo-conditional-payments' ),
				'operators' => array( 'in', 'notin' )
			),
		);

		$filters['general']['filters'] = array_merge( $filters['general']['filters'], $general_filters );

		// Billing email filter
		$filters['billing_address']['filters']['billing_email'] = array(
			'title' => __( 'Email (billing)', 'woo-conditional-payments' ),
			'operators' => array( 'is', 'isnot', 'exists', 'notexists' ),
		);

		// Billing phone filter
		$filters['billing_address']['filters']['billing_phone'] = array(
			'title' => __( 'Phone (billing)', 'woo-conditional-payments' ),
			'operators' => array( 'is', 'isnot', 'exists', 'notexists' ),
		);

		$customer_filters = array(
			'title' => __( 'Customer', 'woo-conditional-payments' ),
			'filters' => array(
				'customer_authenticated' => array(
					'title' => __( 'Logged in / out', 'woo-conditional-payments' ),
					'operators' => array( 'loggedin', 'loggedout' ),
				),
				'customer_role' => array(
					'title' => __( 'Role', 'woo-conditional-payments' ),
					'operators' => array( 'is', 'isnot' ),
				),
				'orders' => array(
					'title' => __( 'Previous orders', 'woo-conditional-payments' ),
					'operators' => array( 'gt', 'gte', 'lt', 'lte' ),
				),
				'vat_exempt' => [
					'title' => __( 'VAT exempt', 'woo-conditional-payments' ),
					'operators' => [ 'is', 'isnot' ],
				],
			),
		);

		// WooCommerce Germanized Pro
		if ( class_exists( 'WooCommerce_Germanized_Pro' ) ) {
			$customer_filters['filters']['vat_id_germanized'] = [
				'title' => __( 'VAT ID (Germanized for WooCommerce)', 'woo-conditional-payments' ),
				'operators' => [ 'exists', 'notexists' ],
			];
		}

		// https://wordpress.org/plugins/woocommerce-eu-vat-assistant/
		if ( class_exists( '\Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant' ) ) {
			$customer_filters['filters']['vat_number_aelia'] = [
				'title' => __( 'VAT number (Aelia EU VAT Assistant)', 'woo-conditional-payments' ),
				'operators' => [ 'exists', 'notexists' ],
			];
		}

		// https://wordpress.org/plugins/woolab-ic-dic/
		if ( function_exists( 'woolab_icdic_init' ) ) {
			$customer_filters['filters']['woolab_billing_ic'] = [
				'title' => __( 'Business ID (Kybernaut IČO DIČ)', 'woo-conditional-payments' ),
				'operators' => [ 'exists', 'notexists' ],
				'callback' => [ 'Woo_Conditional_Payments_Filters_Pro', 'filter_woolab_fields' ],
			];
			
			$customer_filters['filters']['woolab_billing_dic'] = [
				'title' => __( 'Tax ID (Kybernaut IČO DIČ)', 'woo-conditional-payments' ),
				'operators' => [ 'exists', 'notexists' ],
				'callback' => [ 'Woo_Conditional_Payments_Filters_Pro', 'filter_woolab_fields' ],
			];
			
			$customer_filters['filters']['woolab_billing_dic_dph'] = [
				'title' => __( 'VAT reg. no. (Kybernaut IČO DIČ)', 'woo-conditional-payments' ),
				'operators' => [ 'exists', 'notexists' ],
				'callback' => [ 'Woo_Conditional_Payments_Filters_Pro', 'filter_woolab_fields' ],
			];
		}

		$filters['customer'] = $customer_filters;

		$language_filters = array(
      'title' => __( 'Language', 'woo-conditional-payments' ),
      'filters' => array(
        'lang_polylang' => array(
          'title' => __( 'Language - Polylang (inactive)', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' ),
        ),
        'lang_wpml' => array(
          'title' => __( 'Language - WPML (inactive)', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' ),
        ),
      ),
		);
		
		$filters['language'] = $language_filters;

		// Polylang language
		if ( function_exists( 'pll_the_languages' ) ) {
			$filters['language']['filters']['lang_polylang']['title'] = __( 'Language - Polylang (active)', 'woo-conditional-payments' );
		}
	
		// WPML language
		if ( function_exists( 'icl_object_id' ) ) {
			$filters['language']['filters']['lang_wpml']['title'] = __( 'Language - WPML (active)', 'woo-conditional-payments' );
		}
	
		// Groups (https://wordpress.org/plugins/groups/)
		if ( defined( 'GROUPS_CORE_VERSION' ) ) {
			$filters['customer']['filters']['groups'] = array(
				'title' => __( 'Groups', 'woo-conditional-payments' ),
				'operators' => array( 'in', 'notin' ),
			);
		}

		return $filters;
	}

	/**
	 * Store custom fields by 3rd party plugins to session so that they can be used in filters
	 */
	public function store_custom_fields( $post_data ) {
		$data = [];
		parse_str( $post_data, $data );

		if ( ! is_array( $data ) ) {
			return;
		}

		// Aelia EU VAT Assistant
		if ( isset( $data['vat_number'] ) ) {
			WC()->session->set( 'wcp_vat_number', $data['vat_number'] );
		} else {
			WC()->session->__unset( 'wcp_vat_number' );
		}

		// VAT ID / Germanized for WooCommerce Pro
		if ( isset( $data['billing_vat_id'] ) ) {
			WC()->session->set( 'wcp_vat_id', $data['billing_vat_id'] );
		} else {
			WC()->session->__unset( 'wcp_vat_id' );
		}

		// Kybernaut IČO DIČ fields
		$woolab_fields = [ 'billing_ic', 'billing_dic', 'billing_dic_dph' ];
		foreach ( $woolab_fields as $field ) {
			if ( isset( $data[$field] ) ) {
				WC()->session->set( 'wcp_woolab_' . $field, $data[$field] );
			} else {
				WC()->session->__unset( 'wcp_woolab_' . $field );
			}
		}
	}
}
