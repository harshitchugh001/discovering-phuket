<tr valign="top" class="">
	<th scope="row" class="titledesc">
		<label>
			<?php esc_html_e( 'License key', 'woo-conditional-payments' ); ?>
		</label>
	</th>
	<td class="forminp">
		<input type="text" name="license_conditional_payments_for_woocommerce_pro" value="<?php echo esc_attr( $license_key ); ?>" />
		<p class="description"><?php printf( __( 'You can re-order your license key <a href="%s" target="_blank">here</a> if you don\'t have it.', 'woo-stock-sync' ), 'https://wooelements.com/get-your-license-keys/' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row" class="titledesc">
		<label><?php _e( 'License status', 'woo-conditional-payments' ); ?></label>
	</th>
	<td class="forminp">
		<div>
			<?php if ( $status ) { ?>
				<span class="we-license-status-label license-ok"><?php _e( 'Active', 'woo-conditional-payments' ); ?></span>	
			<?php } else if ( $status_unknown ) { ?>
				<span class="we-license-status-label license-unknown"><?php _e( 'Unknown', 'woo-conditional-payments' ); ?></span>	
			<?php } else { ?>
				<span class="we-license-status-label license-disabled"><?php _e( 'Not active', 'woo-conditional-payments' ); ?></span>
				<?php if ( $error ) { ?>
					<span class="we-license-error">(<?php echo $error; ?>)</span>
				<?php } ?>
			<?php } ?>
		</div>

		<?php if ( $last_checked ) { ?>
			<div class="we-license-last-checked">
				<?php printf( __( 'Last checked: %s', 'woo-conditional-payments' ), get_date_from_gmt( date( 'Y-m-d H:i:s', $last_checked ), 'F j, Y g:i a' ) ); ?>
			</div>
		<?php } ?>
	</td>
</tr>
