<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $user_ID;

$message = $license_key = '';



if( isset( $_POST['submit'] ) ) {

    $license_key = !empty( $_POST['license_key'] ) ? sanitize_text_field($_POST['license_key']) : '';


    $rest_url = SHOUTOUTGLOBAL_WOO_REST_URL;

 
	try {

        // var_dump($license_key);

          $response = wp_remote_post( SHOUTOUTGLOBAL_WOO_REST_URL. $license_key );

            if( !empty( $response ) && $response !== 'Access Denied' ) {

                update_option( '_store_access_key', $license_key );

            } else {

                $message = __( 'Please enter valid license key.', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN );
            }

	} catch (\RuntimeException $ex) {
	    die(sprintf('Http error %s with code %d', $ex->getMessage(), $ex->getCode()));
	}
}

// added by sabweb 02/21 for multisite

if (function_exists('is_multisite') && is_multisite()) {
            
                global $wpdb;
                $blog_id = $wpdb->blogid;
            
                switch_to_blog($blog_id);
         $store_link = get_option( '_store_access_key' ); 
          
} else {   
            
        $store_link = get_option( '_store_access_key' ); 
}
$site = '&siteurl='.get_site_url();
?>


<div class="shoutout-global-shoutout wrap">
    <img src="<?php echo SHOUTOUTGLOBAL_WOO_URL . '/assets/images/logo.png'; ?>" class="shoutout-global-logo">

    <?php if( !empty( $store_link ) ) {

        echo '<h1>' . __( 'Account Activated', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ) . '</h1>';
        echo '<div class="shoutout-global-act-link-container"><a href="' . SHOUTOUTGLOBAL_WOO_STORE_LINK . $store_link . $site . '" target="_blank" class="shoutout_global_activated_link"> ' . __( 'Click here to open ShoutOut Admin', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ) . '</a></div>';
        echo '</div>';

        

    } else { ?>

    <h1><?php _e( 'Account Activation', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ); ?></h1>
    <p><?php _e( '<a style="color: #be1e2d;font-weight: 600;font-size: 120%;" target="_blank" href="https://www.shoutout.global/create-woocommerce-affiliate-account.html">Click here for your FREE activation key</a>', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ); ?> </p>
    <p><?php _e( 'Please enter your activation license key that was emailed to you after signup: ', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ); ?> </p>
    <form method="post" id="shoutout-global-register" data-toggle="validator" role="form">

        <?php if( !empty( $message ) ) { ?>
        <div class="alert alert-danger">
          <?php echo isset( $message ) ? $message : ''; ?>
        </div>
        <?php } ?>

        <div class="form-group">
            <label for="license_key"><?php _e( 'License Key', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ); ?><span class="input-error">*</span></label>
            <input type="text" name="license_key" id="license_key" class="form-control" required value="<?php echo $license_key; ?>">
        </div>
        <button type="submit" class="btn btn-primary" name="submit" value="Submit"><?php _e( 'Submit', SHOUTOUTGLOBAL_WOO_TEXTDOMAIN ); ?> </button>

    </form>
    <?php } ?>
</div>