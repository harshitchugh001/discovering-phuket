<?php

// edits by sabweb 
// line 56 & line 76
// added total tax to be sent to shopify

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Public Collection Pages Class
 * 
 * Handles all the different features and functions
 * for the front end pages.
 * 
 * @package ShoutOut
 * @since 1.0.0
 */

if( !class_exists( 'ShoutOut_Global_Public' ) ) { // If class not exist


add_action( 'rest_api_init', 'myplugin_register_routes' );

/**
 * Register the /wp-json/myplugin/v1/foo route
 */
function myplugin_register_routes() {
    

    register_rest_route( 'so_discount/v1', '/add_discount/', array(
        'methods' => 'POST',
        'callback' => 'so_discount_route_1',
        'permission_callback' => '__return_true'
    ) );
}

/**
 * Generate results for the /wp-json/myplugin/v1/foo route.
 *
 * @param WP_REST_Request $request Full details about the request.
 *
 * @return WP_REST_Response|WP_Error The response for the request.
 */


/* added by sabweb Jan2020 */
function so_discount_route_1( WP_REST_Request $request ) {

	$so_secretkey_hashed = $request->get_param( 'so_secretkey_hashed' ); 
	$so_coupon_code = $request->get_param( 'coupon_code' );
	$so_amount = $request->get_param( 'amount' );
	$so_discount_type = $request->get_param( 'discount_type' );
	$so_usage_limit_per_user = $request->get_param( 'usage_limit_per_user' );

	

/**
 * Create a coupon programatically
 */
$coupon_code = $so_coupon_code; // Code
$amount = $so_amount; // Amount
$discount_type = $so_discount_type; // Type: fixed_cart, percent, fixed_product, percent_product

if ($so_usage_limit_per_user == 0)
{
	$so_usage_limit_per_user_det = '';
} elseif ($so_usage_limit_per_user == 1){

	$so_usage_limit_per_user_det = 1;
}

// added by sabweb 02/21 for multisite

if (function_exists('is_multisite') && is_multisite()) {
	global $wpdb;
    
    $blog_id = $wpdb->blogid;
	
	switch_to_blog($blog_id);
	
	$key_key = get_option( '_store_access_key' ); 
	
	} else {   
    
    	$key_key = get_option( '_store_access_key' ); 
	
	}






// echo substr($so_secretkey_hashed,0,-5). ' ' . $key_key;


if(strcmp(substr($so_secretkey_hashed,0,-5),$key_key) == 0 )

{


$coupon = array(
	'post_title' => $coupon_code,
	'post_content' => '',
	'post_status' => 'publish',
	'post_author' => 1,
	'post_type'		=> 'shop_coupon'
);
					
$new_coupon_id = wp_insert_post( $coupon );
					
// Add meta
update_post_meta( $new_coupon_id, 'discount_type', $so_discount_type );
update_post_meta( $new_coupon_id, 'coupon_amount', $so_amount );
update_post_meta( $new_coupon_id, 'product_ids', '' );
update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
update_post_meta( $new_coupon_id, 'expiry_date', '' );
update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
update_post_meta( $new_coupon_id, 'usage_limit_per_user', $so_usage_limit_per_user_det );





	return new WP_Error( 'discount_created', __('Discount Created'), array( 'status' => 200 ) );
} else {

 	return new WP_Error( 'unauthorized', __('Unauthorized'), array( 'status' => 401 ) );


 }

}




	
	class ShoutOut_Global_Public {
		
		public function shoutout_global__construct() {
			
		}

		/**
		 * 
		 * Post order data to thank you page.
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function shoutout_global_order_page( $order ) {

			global $user_ID;

			if( WOOCOMMERCE_VERSION < '3.0.0' ) {
				$order_id	= isset( $order->id ) ? $order->id : '';
			} else {
				$order_id	= $order->get_id();
			}

			// Get an instance of the WC_Order object
			$orderobj = wc_get_order($order_id);

			$_order = $order->get_data();
            
			// added by sabweb 02/21 for multisite

			if (function_exists('is_multisite') && is_multisite()) {
			
				global $wpdb;
		    	$blog_id = $wpdb->blogid;
			
				switch_to_blog($blog_id);
				$access_key = get_option( '_store_access_key' ); 
			
			} else {   
		    
		    	$access_key = get_option( '_store_access_key' ); 
			}
            


            // added by sabweb 190828 to determine item count no. within order
            $item_count = $order->get_item_count();
            // echo $item_count;
            $counter = 0;
            
            
			if( !empty( $access_key ) ) {

				$first_name		= !empty( $_order['shipping']['first_name'] ) ? $_order['shipping']['first_name'] : $_order['billing']['first_name'];
				$last_name		= !empty( $_order['shipping']['last_name'] ) ? $_order['shipping']['last_name'] : $_order['billing']['last_name'];
				$email			= !empty( $_order['billing']['email'] ) ? $_order['billing']['email'] : '';
				$total_price	= !empty( $_order['total'] ) ? $_order['total'] : '';
				$shipping_total	= !empty( $_order['shipping_total'] ) ? $_order['shipping_total'] : 0;
				// added by sabweb 080619 
				$total_tax = !empty( $_order['total_tax'] ) ? $_order['total_tax'] : 0;

                
                if( WOOCOMMERCE_VERSION >= '3.7.0' ) {
                $coupon        = $order->get_coupon_codes();
                } else {
                    
                    $coupon            = $order->get_used_coupons();

                }
                
                $coupon_code	= !empty( $coupon[0] ) ? $coupon[0] : null;

				$store_name 	= $access_key . '.woocommerce.com';
				$currency 		= get_user_meta( $user_ID, '_currency', true );

                $item_info = "xyxyxy[";

                
				// Iterating through each WC_Order_Item_Product objects
					foreach ($orderobj->get_items() as $item_key => $item ):

					    ## Using WC_Order_Item methods ##

					    // Item ID is directly accessible from the $item_key in the foreach loop or
					    $item_id = $item->get_id();

					    ## Using WC_Order_Item_Product methods ##

					    $product      = $item->get_product(); // Get the WC_Product object

					    $product_id   = $item->get_product_id(); // the Product id
					    $variation_id = $item->get_variation_id(); // the Variation id
                
					    $item_type    = $item->get_type(); // Type of the order item ("line_item")

					    $item_name    = $item->get_name(); // Name of the product
					    $quantity     = $item->get_quantity();  
					    $tax_class    = $item->get_tax_class();
					    $line_subtotal     = $item->get_subtotal(); // Line subtotal (non discounted)
					    $line_subtotal_tax = $item->get_subtotal_tax(); // Line subtotal tax (non discounted)
					    $line_total        = $item->get_total(); // Line total (discounted)
					    $line_total_tax    = $item->get_total_tax(); // Line total tax (discounted)

                
                
					    ## Access Order Items data properties (in an array of values) ##
					    $item_data    = $item->get_data();
                
                        // $discount_type = $item->discount_type;
               
                
                
					    $product_name = $item_data['name'];
					    $product_id   = $item_data['product_id'];
					    $variation_id = $item_data['variation_id'];
					    $quantity     = $item_data['quantity'];
					    $tax_class    = $item_data['tax_class'];
					    $line_subtotal     = $item_data['subtotal'];
					    $line_subtotal_tax = $item_data['subtotal_tax'];
					    $line_total        = $item_data['total'];
					    $line_total_tax    = $item_data['total_tax'];

					    // Get data from The WC_product object using methods (examples)
					    $product        = $item->get_product(); // Get the WC_Product object

					    $product_type   = $product->get_type();
					    $product_sku    = $product->get_sku();
					    $product_price  = $product->get_price();
					    $stock_quantity = $product->get_stock_quantity();
                
                // added by sabweb 190828 to determine individual item information
                
                $item_info .= '{ "quantity":'.$quantity.','.'"sku":'.'"'.$product_sku.'"'.','.'"price":'.'"'.$product_price.'",';
                
                        // echo "<br>".$product_sku."<br>";
                        // echo $product_price."<br>";
                        // echo $quantity."<br>";
                        // echo $line_subtotal."<br>";
                        // echo $line_total."<br>";
                        // echo $line_subtotal_tax."<br>";
                
                        $item_discount = $line_total - $line_subtotal;
                
                
                        // echo "discount ".$item_discount;
                
                $item_info .= '"discount_allocations": [{"amount":"'.$item_discount.'"}],';
                $item_info .= '"tax_lines": [{"price":"'.$line_subtotal_tax.'"}]}';
                
                if($counter<$item_count-1){
                    $item_info .= ',';
                    
                }
                
                $counter++;
                
                

					endforeach;
                $item_info .= ']';
                // echo $item_info;
                
				
				wp_localize_script( 'shoutout-global-shoutout-cart', 'Shopify', array(
																'ajaxurl'	=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																'shop'		=> $store_name,
																'checkout'	=> array( 	
																						'order_id' 			=> $order_id,
																						'shipping_address' 	=> array( 'first_name' => $first_name, 'last_name' => $last_name ),
																						'total_price'		=> $total_price,
																						'email'				=> $email,
																						'currency'			=> $currency,
																						'discount'			=> array('code' => $coupon_code ),
																						'shipping_rate'		=> array('price' => $shipping_total ),
																						// added by sabweb 190608
																						'total_tax'		=> $total_tax,
                                                                                        // added by sabweb 190828
                                                                                        'line_items'    => $item_info
																				),
															));
			}
		}

		/**
		 * Add Public Hook
		 * 
		 * Handle to add public hooks
		 * 
		 * @package ShoutOut
		 * @since 1.0.0
		 */
		public function add_hooks() {

			// add action to add an extra detail on order confirmation page
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'shoutout_global_order_page' ), 10, 1 );
		}
	}
}