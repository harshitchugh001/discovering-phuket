<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get rulesets
 */
function woo_conditional_payments_get_rulesets( $only_enabled = false ) {
	$args = array(
		'post_status' => array( 'publish' ),
		'post_type' => 'wcp_ruleset',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	);

  $posts = get_posts( $args );
  
  $rulesets = array();
  foreach ( $posts as $post ) {
    $ruleset = new Woo_Conditional_Payments_Ruleset( $post->ID );

    if ( ! $only_enabled || $ruleset->get_enabled() ) {
      $rulesets[] = $ruleset;
    }
  }

  return $rulesets;
}

/**
 * Get a list of operators
 */
function woo_conditional_payments_operators() {
  return array(
    'gt' => __( 'greater than', 'woo-conditional-payments' ),
    'gte' => __( 'greater than or equal', 'woo-conditional-payments' ),
    'lt' => __( 'less than', 'woo-conditional-payments' ),
    'lte' => __( 'less than or equal', 'woo-conditional-payments' ),
    'in' => __( 'includes', 'woo-conditional-payments' ),
    'exclusive' => __( 'includes (exclusive)', 'woo-conditional-payments' ),
    'notin' => __( 'excludes', 'woo-conditional-payments' ),
    'is' => __( 'is', 'woo-conditional-payments' ),
    'isnot' => __( 'is not', 'woo-conditional-payments' ),
    'exists' => __( 'is not empty', 'woo-conditional-payments' ),
    'notexists' => __( 'is empty', 'woo-conditional-payments' ),
    'contains' => __( 'contains', 'woo-conditional-payments' ),
    'loggedin' => __( 'logged in', 'woo-conditional-payments' ),
    'loggedout' => __( 'logged out', 'woo-conditional-payments' ),
  );
}

/**
 * Get a list of filters
 */
function woo_conditional_payments_filters() {
  $groups = woo_conditional_payments_filter_groups();

  $filters = array();
  foreach ( $groups as $group ) {
    foreach ( $group['filters'] as $key => $filter ) {
      $filters[$key] = $filter;
    }
  }

  return $filters;
}

/**
 * Get a list of filter groups
 */
function woo_conditional_payments_filter_groups() {
  $filters = array(
    'general' => array(
      'title' => __( 'General', 'woo-conditional-payments' ),
      'filters' => array(
        'subtotal' => array(
          'title' => __( 'Order Subtotal', 'woo-conditional-payments' ),
          'operators' => array( 'gt', 'gte', 'lt', 'lte' ),
        ),
        'products' => array(
          'title' => __( 'Products', 'woo-conditional-payments' ),
          'operators' => array( 'in', 'exclusive', 'notin' )
        ),
        'shipping_method' => array(
          'title' => __( 'Shipping Method', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' )
        ),
      ),
    ),
    'billing_address' => array(
      'title' => __( 'Billing Address', 'woo-conditional-payments' ),
      'filters' => array(
        'billing_first_name' => array(
          'title' => __( 'First Name (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'billing_last_name' => array(
          'title' => __( 'Last Name (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'billing_company' => array(
          'title' => __( 'Company (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'billing_state' => array(
          'title' => __( 'State (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' ),
        ),
        'billing_country' => array(
          'title' => __( 'Country (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' ),
        ),
        'billing_address_1' => array(
          'title' => __( 'Address (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'billing_address_2' => array(
          'title' => __( 'Address 2 (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'billing_city' => array(
          'title' => __( 'City (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'billing_postcode' => array(
          'title' => __( 'Postcode (billing)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'is' ),
        ),
      ),
    ),
    'shipping_address' => array(
      'title' => __( 'Shipping Address', 'woo-conditional-payments' ),
      'filters' => array(
        'shipping_first_name' => array(
          'title' => __( 'First Name (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'shipping_last_name' => array(
          'title' => __( 'Last Name (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'shipping_company' => array(
          'title' => __( 'Company (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'shipping_state' => array(
          'title' => __( 'State (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' ),
        ),
        'shipping_country' => array(
          'title' => __( 'Country (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'is', 'isnot' ),
        ),
        'shipping_address_1' => array(
          'title' => __( 'Address (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'shipping_address_2' => array(
          'title' => __( 'Address 2 (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'shipping_city' => array(
          'title' => __( 'City (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'contains' ),
        ),
        'shipping_postcode' => array(
          'title' => __( 'Postcode (shipping)', 'woo-conditional-payments' ),
          'operators' => array( 'exists', 'notexists', 'is' ),
        ),
      ),
    ),
  );

  return apply_filters( 'woo_conditional_payments_filters', $filters );
}

/**
 * Get a list of actions
 */
function woo_conditional_payments_actions() {
  return apply_filters( 'woo_conditional_payments_actions', array(
    'enable_payment_methods' => array(
      'title' => __( 'Enable payment methods', 'woo-conditional-payments' ),
    ),
    'disable_payment_methods' => array(
      'title' => __( 'Disable payment methods', 'woo-conditional-payments' ),
    ),
  ) );
}

/**
 * Get all payment methods to be used in a select field
 */
function woo_conditional_payments_get_payment_method_options() {
  $gateways = WC()->payment_gateways->payment_gateways();

  $options = array();

  foreach ( $gateways as $id => $gateway ) {
    $options[$id] = $gateway->get_method_title() ? $gateway->get_method_title() : $id;
  }

  return $options;
}

/**
 * Get payment gateway instance
 */
function woo_conditional_payments_get_payment_method( $id ) {
  $gateways = WC()->payment_gateways->payment_gateways();

  if ( isset( $gateways[$id] ) ) {
    return $gateways[$id];
  }

  return null;
}

/**
 * Get gateway title
 */
function woo_conditional_payments_get_method_title( $id ) {
  $gateway = woo_conditional_payments_get_payment_method( $id );

  if ( $gateway ) {
    return $gateway->get_method_title();
  }

  return __( 'N/A', 'woo-conditional-payments' );
}

/**
 * Get action title
 */
function woo_conditional_payments_action_title( $action_id ) {
  $actions = woo_conditional_payments_actions();

  if ( isset( $actions[$action_id] ) ) {
    return $actions[$action_id]['title'];
  }

  return __( 'N/A', 'woo-conditional-shipping' );
}

/**
 * Get settings link for a gateway
 */
function woo_conditional_payments_get_gateway_url( $gateway ) {
  return add_query_arg( array(
    'page' => 'wc-settings',
    'tab' => 'checkout',
    'section' => $gateway->id,
  ), admin_url( 'admin.php' ) );
}

/**
 * Format ruleset IDs into a list of links
 */
function woo_conditional_payments_format_ruleset_ids( $ids ) {
  $items = array();

  foreach ( $ids as $id ) {
    $ruleset = new Woo_Conditional_Payments_Ruleset( $id );

    if ( $ruleset->get_post() ) {
      $items[] = sprintf( '<a href="%s" target="_blank">%s</a>', $ruleset->get_admin_edit_url(), $ruleset->get_title() );
    }
  }

  return implode( ', ', $items );
}

/**
 * Load all shipping methods to be used in a select field
 */
function woo_conditional_payments_get_shipping_method_options() {
  $shipping_zones = array( new WC_Shipping_Zone( 0 ) );
  $shipping_zones = array_merge( $shipping_zones, WC_Shipping_Zones::get_zones() );
  $options = array();

  foreach ( $shipping_zones as $shipping_zone ) {
    if ( is_array( $shipping_zone ) && isset( $shipping_zone['zone_id'] ) ) {
      $shipping_zone = WC_Shipping_Zones::get_zone( $shipping_zone['zone_id'] );
    } else if ( ! is_object( $shipping_zone ) ) {
      // Skip
      continue;
    }

    $methods = array();
    foreach ( $shipping_zone->get_shipping_methods() as $shipping_method ) {
      if ( method_exists( $shipping_method, 'get_rate_id' ) ) {
        $methods[] = array(
          'title' => $shipping_method->title,
          'rate_id' => $shipping_method->get_rate_id(),
          'instance_id' => $shipping_method->get_instance_id(),
          'combined_id' => implode( '&', array( $shipping_method->get_rate_id(), $shipping_method->get_instance_id()) ),
        );
      }
    }

    if ( ! empty( $methods ) ) {
      $options[$shipping_zone->get_id()] = array(
        'title' => $shipping_zone->get_zone_name(),
        'methods' => $methods,
      );
    }
  }

  $options = apply_filters( 'woo_conditional_payments_shipping_method_options', $options );

  return $options;
}

/**
 * Get shipping class options
 */
function woo_conditional_payments_get_shipping_class_options() {
  $shipping_classes = WC()->shipping->get_shipping_classes();
  $shipping_class_options = array();
  foreach ( $shipping_classes as $shipping_class ) {
    $shipping_class_options[$shipping_class->term_id] = $shipping_class->name;
  }

  return $shipping_class_options;
}

/**
 * Get product type options
 */
function wcp_get_product_type_options() {
  $options = [
    'physical' => __( 'Physical products', 'woo-conditional-payments' ),
    'virtual' => __( 'Virtual products', 'woo-conditional-payments' ),
    'downloadable' => __( 'Downloadable products', 'woo-conditional-payments' ),
  ];

  return $options;
}

/**
 * Get order status options
 */
function wcp_order_status_options() {
  if ( ! function_exists( 'wc_get_order_statuses' ) ) {
    return [];
  }

  return wc_get_order_statuses();
}

/**
 * Get category options
 */
function woo_conditional_payments_get_category_options() {
  $categories = get_terms( 'product_cat', array(
    'hide_empty' => false,
  ) );

  $sorted = array();
  woo_conditional_payments_sort_terms_hierarchicaly( $categories, $sorted );

  // Flatten hierarchy
  $options = array();
  woo_conditional_payments_flatten_terms( $options, $sorted );

  return $options;
}

/**
 * Output term tree into a select field options
 */
function woo_conditional_payments_flatten_terms( &$options, $cats, $depth = 0 ) {
  foreach ( $cats as $cat ) {
    if ( $depth > 0 ) {
      $prefix = str_repeat( ' - ', $depth );
      $options[$cat->term_id] = "{$prefix} {$cat->name}";
    } else {
      $options[$cat->term_id] = "{$cat->name}";
    }

    if ( isset( $cat->children ) && ! empty( $cat->children ) ) {
      woo_conditional_payments_flatten_terms( $options, $cat->children, $depth + 1 );
    }
  }
}

/**
 * Sort categories hierarchically
 */
function woo_conditional_payments_sort_terms_hierarchicaly( Array &$cats, Array &$into, $parentId = 0 ) {
  foreach ( $cats as $i => $cat ) {
    if ( $cat->parent == $parentId ) {
      $into[$cat->term_id] = $cat;
      unset( $cats[$i] );
    }
  }

  foreach ( $into as $topCat ) {
    $topCat->children = array();
    woo_conditional_payments_sort_terms_hierarchicaly( $cats, $topCat->children, $topCat->term_id );
  }
}

/**
 * Get coupon options
 */
function woo_conditional_payments_get_coupon_options() {
  $args = array(
    'posts_per_page' => 100, // Only get 100 latest coupons for performance reasons
    'orderby' => 'ID',
    'order' => 'desc',
    'post_type' => 'shop_coupon',
    'post_status' => 'publish',
  );

  $coupons = get_posts( $args );

  $options = array(
    '_all' => __( '- All coupons -', 'woo-conditional-payments' ),
  );
  foreach ( $coupons as $coupon ) {
    $options[$coupon->ID] = $coupon->post_title;
  }

  // Order by code / title
  asort( $options );

  return $options;
}

/**
 * Load all roles to be used in a select field
 */
function woo_conditional_payments_role_options() {
  $options = array();

  if ( function_exists( 'get_editable_roles' ) ) {
    $editable_roles = array_reverse( get_editable_roles() );

    foreach ( $editable_roles as $role => $details ) {
      $name = translate_user_role( $details['name'] );
      $options[$role] = $name;
    }
  }

  return $options;
}

/**
 * Load all groups (from itthinx 3rd party plugin) to be used in a select field
 */
function woo_conditional_payments_groups_options() {
  if ( ! defined( 'GROUPS_CORE_VERSION' ) || ! function_exists( '_groups_get_tablename' ) ) {
    return array();
  }

  global $wpdb;

  $groups_table = _groups_get_tablename( 'group' );
  $groups = $wpdb->get_results( "SELECT * FROM $groups_table ORDER BY name" );

  $options = array();
  if ( $groups ) {
    foreach ( $groups as $group ) {
      $options[$group->group_id] = $group->name;
    }
  }

  return $options;
}

/**
 * Load all Polylang languages to be used in a select field
 */
function woo_conditional_payments_polylang_options() {
  $options = array();

  if ( function_exists( 'pll_languages_list' ) ) {
    $langs = pll_languages_list( array(
      'fields' => NULL, // return all fields
    ) );

    foreach ( $langs as $lang ) {
      $options[$lang->slug] = $lang->name;
    }
  }

  return $options;
}

/**
 * Load all WPML languages to be used in a select field
 */
function woo_conditional_payments_wpml_options() {
  $options = array();

  if ( function_exists( 'icl_object_id' ) ) {
    $langs = apply_filters( 'wpml_active_languages', NULL, 'orderby=name&order=asc' );

    foreach ( $langs as $lang ) {
      $options[$lang['code']] = $lang['translated_name'];
    }
  }

  return $options;
}

/**
 * Country options
 */
function woo_conditional_payments_country_options() {
  $countries_obj = new WC_Countries();

  return $countries_obj->get_countries();
}

/**
 * State options
 */
function woo_conditional_payments_state_options() {
  $countries_obj = new WC_Countries();
  $countries = $countries_obj->get_countries();
  $states = array_filter( $countries_obj->get_states() );

  $options = [];

  foreach ( $states as $country_id => $state_list ) {
    $options[$country_id] = [
      'states' => $state_list,
      'country' => $countries[$country_id],
    ];
  }

  // Move US as first as it is the most commonly used
  $us = $options['US'];
  unset( $options['US'] );
  $options = ['US' => $us] + $options;

  return $options;
}

/**
 * Fee taxation options
 */
function woo_conditional_payments_fee_tax_options() {
  $options = array(
    '_none' => __( '- Not taxable -', 'woo-conditional-payments' ),
  );

  $options += wc_get_product_tax_class_options();

  return $options;
}

/**
 * Get product categories
 */
function woo_conditional_payments_get_product_cats( $product_id ) {
  $cat_ids = array();

  if ( $product = wc_get_product( $product_id ) ) {
    $terms = get_the_terms( $product->get_id(), 'product_cat' );
    if ( $terms ) {
      foreach ( $terms as $term ) {
        $cat_ids[$term->term_id] = true;
      }
    }

    // If this is variable product, append parent product categories
    if ( $product->get_parent_id() ) {
      $terms = get_the_terms( $product->get_parent_id(), 'product_cat' );
      if ( $terms ) {
        foreach ( $terms as $term ) {
          $cat_ids[$term->term_id] = true;
        }
      }
    }

    // Finally add all parent terms
    if ( apply_filters( 'woo_conditional_payments_incl_parent_cats', true ) ) {
      foreach ( array_keys( $cat_ids ) as $term_id ) {
        $ancestors = (array) get_ancestors( $term_id, 'product_cat', 'taxonomy' );

        foreach ( $ancestors as $ancestor_id ) {
          $cat_ids[$ancestor_id] = true;
        }
      }
    }
  }

  return array_keys( $cat_ids );
}
