<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Payments_Ruleset {
  private $post_id;

  /**
   * Constructor
   */
  public function __construct( $post_id = false ) {
    $this->post_id = $post_id;
  }

  /**
   * Get ID
   */
  public function get_id() {
    return $this->post_id;
  }

  /**
   * Get title
   */
  public function get_title( $context = 'view' ) {
    $post = $this->get_post();

    if ( $post && $post->post_title ) {
      return $post->post_title;
    }

    if ( $context === 'edit' ) {
      return '';
    }

    return __( 'Ruleset', 'woo-conditional-payments' );
  }

  /**
   * Get whether or not ruleset is enabled
   */
  public function get_enabled() {
    $enabled = get_post_meta( $this->post_id, '_wcp_enabled', true );
    $enabled_exists = metadata_exists( 'post', $this->post_id, '_wcp_enabled' );

    // Metadata doesn't exist yet so we assume it's enabled
    if ( ! $enabled_exists ) {
      return true;
    }

    return $enabled === 'yes';
  }

  /**
   * Get admin edit URL
   */
  public function get_admin_edit_url() {
    $url = add_query_arg( array(
      'ruleset_id' => $this->post_id,
    ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_conditional_payments' ) );

    return $url;
  }

  /**
   * Get admin delete URL
   */
  public function get_admin_delete_url() {
    $url = add_query_arg( array(
      'ruleset_id' => $this->post_id,
      'action' => 'delete',
    ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_conditional_payments' ) );

    return $url;
  }

  /**
   * Get post
   */
  public function get_post() {
    if ( $this->post_id ) {
      return get_post( $this->post_id );
    }

    return false;
  }

  /**
	 * Get products which are selected in conditions
	 */
	public function get_products() {
    $product_ids = array();

		foreach ( $this->get_conditions() as $condition ) {
			if ( isset( $condition['product_ids'] ) && is_array( $condition['product_ids'] ) ) {
				$product_ids = array_merge( $product_ids, $condition['product_ids'] );
			}
		}

		$products = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$products[$product_id] = wp_kses_post( $product->get_formatted_name() );
			}
		}

		return $products;
  }
  
  /**
   * Get conditions for the ruleset
   */
  public function get_conditions() {
    $conditions = get_post_meta( $this->post_id, '_wcp_conditions', true );

    if ( ! $conditions ) {
      return array();
    }

    return (array) $conditions;
  }

  /**
   * Get actions for the ruleset
   */
  public function get_actions() {
    $actions = get_post_meta( $this->post_id, '_wcp_actions', true );

    if ( ! $actions ) {
      return array();
    }

    return (array) $actions;
  }

  /**
   * Get operator for conditions (AND / OR)
   */
  public function get_conditions_operator() {
    $operator = get_post_meta( $this->post_id, '_wcp_operator', true );

    if ( $operator && in_array( $operator, [ 'and', 'or' ], true ) ) {
      return $operator;
    }

    return 'and';
  }

  /**
   * Check if conditions pass for the given package
   */
  public function validate() {
    $filters = woo_conditional_payments_filters();

    $results = [];
    foreach ( $this->get_conditions() as $index => $condition ) {
      if ( isset( $condition['type'] ) && ! empty( $condition['type'] ) ) {
        $function = "filter_{$condition['type']}";

        if ( isset( $filters[$condition['type']] ) && isset( $filters[$condition['type']]['callback'] ) ) {
          $callable = $filters[$condition['type']]['callback'];
        } else if ( class_exists( 'Woo_Conditional_Payments_Filters_Pro' ) && method_exists( 'Woo_Conditional_Payments_Filters_Pro', $function ) ) {
          $callable = array( 'Woo_Conditional_Payments_Filters_Pro', "filter_{$condition['type']}" );
        } else {
          $callable = array( 'Woo_Conditional_Payments_Filters', "filter_{$condition['type']}" );
        }

        $results[$index] = (bool) call_user_func( $callable, $condition );
      }
    }

    // If operator is OR, it is enough that one condition passed
    if ( $this->get_conditions_operator() === 'or' ) {
      $passed = in_array( false, $results, true ) === true;
    } else {
      $passed = in_array( true, $results, true ) === false;
    }

    return $passed;
  }
}
