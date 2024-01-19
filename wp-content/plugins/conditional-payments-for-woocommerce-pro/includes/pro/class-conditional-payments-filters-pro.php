<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Conditional_Payments_Filters_Pro {
	/**
	 * Filter by number of items
	 */
  public static function filter_items( $condition ) {
		$cart_items = Woo_Conditional_Payments_Filters::get_items_count();

		if ( isset( $condition['value'] ) && ! empty( $condition['value'] ) ) {
			$items = Woo_Conditional_Payments_Filters::_parse_number( $condition['value'] );

			return ! Woo_Conditional_Payments_Filters::_compare_numeric_value( $cart_items, $items, $condition['operator'] );
		}

		return FALSE;
	}

	/**
	 * Filter by previous orders
	 */
	public static function filter_orders( $condition ) {
		if ( ! is_user_logged_in() && isset( $condition['orders_match_guests_by_email'] ) && $condition['orders_match_guests_by_email'] ) {
			return self::filter_orders_for_guests( $condition );
		}

		// Customer not logged in, filter out
		if ( ! is_user_logged_in() ) {
			return TRUE;
		}

		// Get customer ID
		$customer_id = FALSE;
		$user = wp_get_current_user();
		if ( $user ) {
			$customer_id = $user->ID;
		}

		// Bail out if customer ID could not be retrieved
		if ( ! $customer_id ) {
			return TRUE;
		}

		// Statuses
		$statuses = [ 'processing', 'completed' ];
		if ( isset( $condition['orders_status'] ) && ! empty( $condition['orders_status'] ) ) {
			$statuses = $condition['orders_status'];
		}

		// Get customer previous orders
		$orders = wc_get_orders( array(
			'customer_id' => $customer_id,
			'limit' => -1,
			'return' => 'ids',
			'status' => apply_filters( 'woo_conditional_payments_order_condition_statuses', $statuses ),
		) );

		return ! Woo_Conditional_Payments_Filters::_compare_numeric_value( count( $orders ), Woo_Conditional_Payments_Filters::_parse_number( $condition['value'] ), $condition['operator'] );
	}

	/**
	 * Filter by previous orders for guests by matching email
	 */
	private static function filter_orders_for_guests( $condition ) {
		$email = Woo_Conditional_Payments_Filters::_get_order_attr( 'billing_email' );

		// Bail out if email could not be retrieved
		if ( ! $email ) {
			return TRUE;
		}

		// Statuses
		$statuses = [ 'processing', 'completed' ];
		if ( isset( $condition['orders_status'] ) && ! empty( $condition['orders_status'] ) ) {
			$statuses = $condition['orders_status'];
		}

		// Get customer previous orders
		$orders = wc_get_orders( array(
			'billing_email' => $email,
			'limit' => -1,
			'return' => 'ids',
			'status' => apply_filters( 'woo_conditional_payments_order_condition_statuses', $statuses ),
		) );

		return ! Woo_Conditional_Payments_Filters::_compare_numeric_value( count( $orders ), Woo_Conditional_Payments_Filters::_parse_number( $condition['value'] ), $condition['operator'] );
	}

	/**
	 * Filter VAT exempt
	 */
	public static function filter_vat_exempt( $condition ) {
		if ( WC()->customer ) {
			$exempt = WC()->customer->get_is_vat_exempt();
	
			if ( $condition['operator'] === 'is' ) {
				return ! $exempt;
			} else if ( $condition['operator'] === 'isnot' ) {
				return $exempt;
			}
		}
	
		return false;
	}

	/**
	 * Filter VAT number (Aelia EU VAT Assistant)
	 */
	public static function filter_vat_number_aelia( $condition ) {
		if ( WC()->session ) {
			$vat_number = trim( strval( WC()->session->get( 'wcp_vat_number', false ) ) );

			if ( $condition['operator'] === 'exists' ) {
				return empty( $vat_number );
			} else if ( $condition['operator'] === 'notexists' ) {
				return ! empty( $vat_number );
			}
		}
	
		return false;
	}

	/**
	 * Filter VAT ID / Germanized for WooCommerce Pro
	 */
	public static function filter_vat_id_germanized( $condition ) {
		if ( WC()->session ) {
			$value = trim( strval( WC()->session->get( 'wcp_vat_id', false ) ) );

			if ( $condition['operator'] === 'exists' ) {
				return empty( $value );
			} else if ( $condition['operator'] === 'notexists' ) {
				return ! empty( $value );
			}
		}

		return false;
	}

	/**
	 * Filter Kybernaut IČO DIČ fields
	 */
	public static function filter_woolab_fields( $condition ) {
		if ( WC()->session ) {
			$field = sprintf( 'wcp_%s', $condition['type'] );
			$value = trim( strval( WC()->session->get( $field, false ) ) );

			if ( $condition['operator'] === 'exists' ) {
				return empty( $value );
			} else if ( $condition['operator'] === 'notexists' ) {
				return ! empty( $value );
			}
		}

		return false;
	}

	/**
	 * Filter by shipping class
	 */
	public static function filter_shipping_class( $condition ) {
		if ( isset( $condition['shipping_class_ids'] ) && ! empty( $condition['shipping_class_ids'] ) ) {
			$shipping_class_ids = Woo_Conditional_Payments_Filters::_get_order_shipping_class_ids();

			// Cast to integers
			$shipping_class_ids = array_map( 'intval', $shipping_class_ids );
			$condition['shipping_class_ids'] = array_map( 'intval', $condition['shipping_class_ids'] );

			return ! Woo_Conditional_Payments_Filters::_group_comparison( $shipping_class_ids, $condition['shipping_class_ids'], $condition['operator'] );
		}

		return FALSE;
	}

	/**
	 * Filter type product type
	 */
	public static function filter_product_types( $condition ) {
		if ( isset( $condition['product_types'] ) && ! empty( $condition['product_types'] ) ) {
			$product_types = Woo_Conditional_Payments_Filters::get_order_product_types();

			return ! Woo_Conditional_Payments_Filters::_group_comparison( $product_types, $condition['product_types'], $condition['operator'] );
		}

		return false;
	}

	/**
	 * Filter coupons
	 */
	public static function filter_coupon( $condition ) {
		if ( empty( $condition['coupon_ids'] ) ) {
			return FALSE;
		}

		$coupon_ids = Woo_Conditional_Payments_Filters::get_order_coupon_ids();

		return ! Woo_Conditional_Payments_Filters::_group_comparison( $coupon_ids, $condition['coupon_ids'], $condition['operator'] );
	}

	/**
	 * Filter by product categories
	 */
	public static function filter_product_cats( $condition ) {
		if ( isset( $condition['product_cat_ids'] ) && ! empty( $condition['product_cat_ids'] ) ) {
			if ( $condition['operator'] == 'exclusive' ) {
				return self::filter_product_cats_exclusive( $condition );
			}

			$cat_ids = Woo_Conditional_Payments_Filters::_get_order_product_cat_ids();

			return ! Woo_Conditional_Payments_Filters::_group_comparison( $cat_ids, $condition['product_cat_ids'], $condition['operator'] );
		}

		return FALSE;
	}

	/**
	 * Filter exclusive product categories
	 */
	public static function filter_product_cats_exclusive( $condition ) {
		if ( isset( $condition['product_cat_ids'] ) && ! empty( $condition['product_cat_ids'] ) ) {
			$exclusive_category_ids = $condition['product_cat_ids'];

			foreach ( Woo_Conditional_Payments_Filters::_get_order_products() as $product ) {
				$cat_ids = woo_conditional_payments_get_product_cats( $product->get_id() );

				$array_intersect = array_intersect( $exclusive_category_ids, $cat_ids );
				if ( empty( $array_intersect ) ) {
					return true;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Filter by email
	 */
	public static function filter_billing_email( $condition ) {
		if ( in_array( $condition['operator'], [ 'exists', 'notexists'] ) ) {
			return ! Woo_Conditional_Payments_Filters::_text_filtering( 'billing_email', $condition );
		}

		if ( isset( $condition['emails'] ) ) {
			$emails = array_filter( array_map( 'strtolower', array_map( 'wc_clean', explode( "\n", $condition['emails'] ) ) ) );

			if ( ! empty( $emails ) ) {
				$email = strtolower( Woo_Conditional_Payments_Filters::_get_order_attr( 'billing_email' ) );

				return ! Woo_Conditional_Payments_Filters::_is_array_comparison( $email, $emails, $condition['operator'] );
			}
		}

		return false;
	}

	/**
	 * Filter by phone
	 */
	public static function filter_billing_phone( $condition ) {
		if ( in_array( $condition['operator'], [ 'exists', 'notexists'] ) ) {
			return ! Woo_Conditional_Payments_Filters::_text_filtering( 'billing_phone', $condition );
		}

		if ( isset( $condition['phones'] ) ) {
			$phones = array_filter( array_map( 'strtolower', array_map( 'wc_clean', explode( "\n", $condition['phones'] ) ) ) );

			if ( ! empty( $phones ) ) {
				$phone = str_replace( ' ', '', strtolower( Woo_Conditional_Payments_Filters::_get_order_attr( 'billing_phone' ) ) );

				return ! Woo_Conditional_Payments_Filters::_is_array_comparison( $phone, $phones, $condition['operator'] );
			}
		}

		return false;
	}


	/**
	 * Filter customer logged in / out
	 */
	public static function filter_customer_authenticated( $condition ) {
		switch ( $condition['operator'] ) {
			case 'loggedin':
				return ! is_user_logged_in();
			case 'loggedout':
				return is_user_logged_in();
		}

		error_log( "Invalid operator for customer authenticated" );

		return FALSE;
	}

	/**
	 * Filter customer role
	 */
	public static function filter_customer_role( $condition ) {
		if ( empty( $condition['user_roles'] ) ) {
			return FALSE;
		}

		// User not logged in, should filter out
		if ( ! is_user_logged_in() && $condition['operator'] === 'is' ) {
			return TRUE;
		}

		// User is not authenticated and doesn't has role, thus we can pass for "is not" operator
		if ( ! is_user_logged_in() && $condition['operator'] === 'isnot' ) {
			return FALSE;
		}

		$user = wp_get_current_user();
		$roles = (array) $user->roles;
		$roles = array_values( array_filter( $roles ) ); // Remove empty values just in case

		// Originally this function only supported one role per user. However,
		// some 3rd party plugins might add support for multiple roles per user
		// so we will switch operators to group operators
		if ( $condition['operator'] === 'is' ) {
			$condition['operator'] = 'in';
		} else if ( $condition['operator'] === 'isnot' ) {
			$condition['operator'] = 'notin';
		}

		return ! Woo_Conditional_Payments_Filters::_group_comparison( $roles, $condition['user_roles'], $condition['operator'] );
	}

	/**
	 * Filter customer group
	 */
	public static function filter_groups( $condition ) {
		if ( empty( $condition['user_groups'] ) || ! defined( 'GROUPS_CORE_VERSION' ) ) {
			return FALSE;
		}

		// User not logged in, should filter out
		if ( ! is_user_logged_in() && $condition['operator'] === 'in' ) {
			return TRUE;
		}

		// User is not authenticated and doesn't has group, thus we can pass for "not in" operator
		if ( ! is_user_logged_in() && $condition['operator'] === 'notin' ) {
			return FALSE;
		}

		$groups_user = new Groups_User( get_current_user_id() );
		$user_group_ids = (array) $groups_user->group_ids_deep;

		// Cast to integers
		$user_group_ids = array_map( 'intval', $user_group_ids );
		$condition['user_groups'] = array_map( 'intval', $condition['user_groups'] );

		return ! Woo_Conditional_Payments_Filters::_group_comparison( $user_group_ids, $condition['user_groups'], $condition['operator'] );
	}

	/**
	 * Filter Polylang language
	 */
	public static function filter_lang_polylang( $condition ) {
		if ( empty( $condition['lang_polylang'] ) ) {
			return FALSE;
		}

		if ( ! function_exists( 'pll_current_language' ) ) {
			return FALSE;
		}

		return ! Woo_Conditional_Payments_Filters::_is_array_comparison( pll_current_language(), $condition['lang_polylang'], $condition['operator'] );
	}

	/**
	 * Filter WPML language
	 */
	public static function filter_lang_wpml( $condition ) {
		if ( empty( $condition['lang_wpml'] ) ) {
			return FALSE;
		}

		if ( ! function_exists( 'icl_object_id' ) || ! defined( 'ICL_LANGUAGE_CODE' ) ) {
			return FALSE;
		}

		return ! Woo_Conditional_Payments_Filters::_is_array_comparison( ICL_LANGUAGE_CODE, $condition['lang_wpml'], $condition['operator'] );
	}
}
