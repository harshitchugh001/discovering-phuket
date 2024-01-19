<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2 class="woo-conditional-payments-heading">
	<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_conditional_payments' ); ?>">
		<?php _e( 'Conditions', 'woo-conditional-payments' ); ?>
	</a>
	 &gt; 
	<?php echo $ruleset->get_title(); ?>
</h2>

<table class="form-table woo-conditional-payments-ruleset-settings">
	<tbody>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">
				<label>
					<?php esc_html_e( 'Enable / Disable', 'woo-conditional-payments' ); ?>
				</label>
			</th>
			<td class="forminp">
				<input type="checkbox" name="ruleset_enabled" id="ruleset_enabled" value="1" <?php checked( $ruleset->get_enabled() ); ?> />
				<label for="ruleset_enabled"><?php _e( 'Enable ruleset', 'woo-conditional-payments' ); ?></label>
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">
				<label>
					<?php esc_html_e( 'Ruleset name', 'woo-conditional-payments' ); ?>
					<?php echo wc_help_tip( __( 'This is the name of the ruleset for your reference.', 'woo-conditional-payments' ) ); ?>
				</label>
			</th>
			<td class="forminp">
				<input type="text" name="ruleset_name" id="ruleset_name" value="<?php echo esc_attr( $ruleset->get_title( 'edit' ) ); ?>" placeholder="<?php esc_attr_e( 'Ruleset name', 'woo-conditional-payments' ); ?>" />
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">
				<label>
					<?php esc_html_e( 'Conditions', 'woo-conditional-payments' ); ?>
					<?php echo wc_help_tip( __( 'The following conditions define whether or not actions are run.', 'woo-conditional-payments' ) ); ?>
				</label>
			</th>
			<td class="">
				<table
					class="woo-conditional-payments-conditions widefat"
					data-operators="<?php echo htmlspecialchars( json_encode( woo_conditional_payments_operators() ), ENT_QUOTES, 'UTF-8' ); ?>"
					data-selected-products="<?php echo htmlspecialchars( json_encode( $ruleset->get_products() ), ENT_QUOTES, 'UTF-8' ); ?>"
					data-conditions="<?php echo htmlspecialchars( json_encode( $ruleset->get_conditions() ), ENT_QUOTES, 'UTF-8' ); ?>"
				>
					<tbody class="woo-conditional-payments-condition-rows">
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4" class="forminp">
								<button type="button" class="button" id="wcp-add-condition"><?php _e( 'Add Condition', 'woo-conditional-payments' ); ?></button>
								<button type="button" class="button" id="wcp-remove-conditions"><?php _e( 'Remove Selected', 'woo-conditional-payments' ); ?></button>
								<select name="wcp_operator">
									<option value="and" <?php selected( 'and', $ruleset->get_conditions_operator() ); ?>><?php _e( 'All conditions have to pass (AND)', 'woo-conditional-shipping' ); ?></option>
									<option value="or" <?php selected( 'or', $ruleset->get_conditions_operator() ); ?>><?php _e( 'One condition has to pass (OR)', 'woo-conditional-shipping' ); ?></option>
								</select>
							</td>
						</tr>
					</tfoot>
				</table>
				<?php if ( ! class_exists( 'Woo_Conditional_Payments_Pro' ) ) { ?>
					<p class="description conditions-desc">
						<?php printf( __( 'More conditions available in <a href="%s" target="_blank">the Pro version</a>.', 'woo-conditional-payments' ), 'https://wooelements.com/products/conditional-payments' ); ?>
					</p>
				<?php } ?>
			</td>
		</tr>
		<tr valign="top" class="">
			<th scope="row" class="titledesc">
				<label>
					<?php esc_html_e( 'Actions', 'woo-conditional-payments' ); ?>
					<?php echo wc_help_tip( __( 'Actions which are run if all conditions pass.', 'woo-conditional-payments' ) ); ?>
				</label>
			</th>
			<td class="">
				<table
					class="woo-conditional-payments-actions widefat"
					data-actions="<?php echo htmlspecialchars( json_encode( $ruleset->get_actions() ), ENT_QUOTES, 'UTF-8' ); ?>"
				>
					<tbody class="woo-conditional-payments-action-rows">
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4" class="forminp">
								<button type="button" class="button" id="wcp-add-action"><?php _e( 'Add Action', 'woo-conditional-payments' ); ?></button>
								<button type="button" class="button" id="wcp-remove-actions"><?php _e( 'Remove Selected', 'woo-conditional-payments' ); ?></button>
							</td>
						</tr>
					</tfoot>
				</table>
				<p class="description actions-desc">
					<?php _e( '<strong>Enable payment methods</strong>: Payment methods will be enabled if all conditions pass. If conditions do not pass, payment methods will be disabled.', 'woo-conditional-payments' ); ?><br>
					<?php _e( '<strong>Disable payment methods</strong>: Payment methods will be disabled if all conditions pass.', 'woo-conditional-payments' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>

<p class="submit">
	<button type="submit" name="submit" id="submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save changes', 'woo-conditional-payments' ); ?>"><?php esc_html_e( 'Save changes', 'woo-conditional-payments' ); ?></button>

	<input type="hidden" value="<?php echo $ruleset->get_id(); ?>" name="ruleset_id" />
	<input type="hidden" value="1" name="save" />

	<?php wp_nonce_field( 'woocommerce-settings' ); ?>
</p>

<script type="text/html" id="tmpl-wcp_row_template">
	<tr valign="top" class="condition_row">
		<th class="condition_remove">
			<input type="checkbox" class="remove_condition">
		</th>
		<th scope="row" class="titledesc">
			<fieldset>
				<select name="wcp_conditions[{{data.index}}][type]" class="wcp_condition_type_select">
					<?php foreach ( woo_conditional_payments_filter_groups() as $filter_group ) { ?>
						<optgroup label="<?php echo $filter_group['title']; ?>">
							<?php foreach ( $filter_group['filters'] as $key => $filter ) { ?>
								<option
									value="<?php echo $key; ?>"
									data-operators="<?php echo htmlspecialchars( json_encode( $filter['operators'] ), ENT_QUOTES, 'UTF-8'); ?>"
									<# if ( data.type == '<?php echo $key; ?>' ) { #>selected<# } #>
								>
									<?php echo $filter['title']; ?>
								</option>
							<?php } ?>
						</optgroup>
					<?php } ?>
				</select>
			</fieldset>
		</th>
		<td class="forminp">
			<select class="wcp_operator_select" name="wcp_conditions[{{data.index}}][operator]">
				<?php foreach ( woo_conditional_payments_operators() as $key => $operator ) { ?>
					<option
						value="<?php echo $key; ?>"
						class="wcp-operator wcp-operator-<?php echo $key; ?>"
						<# if ( data.operator == '<?php echo $key; ?>' ) { #>selected<# } #>
					>
						<?php echo $operator; ?>
					</option>
				<?php } ?>
			</select>
		</td>
		<td class="forminp">
			<fieldset class="wcp_condition_value_inputs">
				<input class="input-text value_input regular-input wcp_text_value_input" type="text" name="wcp_conditions[{{data.index}}][value]" value="{{data.value}}" />

				<div class="value_input wcp_postcode_value_input">
					<textarea name="wcp_conditions[{{data.index}}][postcodes]" class="" placeholder="<?php esc_attr_e( 'List 1 postcode per line', 'woocommerce' ); ?>">{{ data.postcodes }}</textarea>

					<div class="description"><?php _e( 'Postcodes containing wildcards (e.g. CB23*) or fully numeric ranges (e.g. <code>90210...99000</code>) are also supported.', 'woo-conditional-shipping' ); ?></div>
				</div>

				<div class="value_input wcp_billing_email_value_input">
					<textarea name="wcp_conditions[{{data.index}}][emails]" class="" placeholder="<?php esc_attr_e( 'List 1 email address per line', 'woocommerce' ); ?>">{{ data.emails }}</textarea>
				</div>

				<div class="value_input wcp_billing_phone_value_input">
					<textarea name="wcp_conditions[{{data.index}}][phones]" class="" placeholder="<?php esc_attr_e( 'List 1 phone number per line', 'woo-conditional-payments' ); ?>">{{ data.phones }}</textarea>
				</div>

				<div class="value_input wcp_subtotal_value_input">
					<input type="checkbox" id="wcp-subtotal-includes-coupons-{{data.index}}" value="1" name="wcp_conditions[{{data.index}}][subtotal_includes_coupons]" <# if ( data.subtotal_includes_coupons ) { #>checked<# } #> />
					<label for="wcp-subtotal-includes-coupons-{{data.index}}"><?php _e( 'Subtotal includes coupons', 'woo-conditional-payments' ); ?></label>
				</div>

				<div class="value_input wcp_orders_value_input">
					<div class="wcp_orders_status_input">
						<select name="wcp_conditions[{{data.index}}][orders_status][]" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Order statuses', 'woo-conditional-payments' ); ?>">
							<?php foreach( wcp_order_status_options() as $value => $label ) { ?>
								<option
									value="<?php echo esc_attr( $value ); ?>"
									<# if ( data.orders_status && jQuery.inArray( '<?php echo esc_attr( $value ); ?>', data.orders_status ) !== -1 ) { #>
										selected
									<# } #>
								>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php } ?>
						</select>
					</div>

					<div>
						<input type="checkbox" id="wcp-orders-match-guests-by-email-{{data.index}}" value="1" name="wcp_conditions[{{data.index}}][orders_match_guests_by_email]" <# if ( data.orders_match_guests_by_email ) { #>checked<# } #> />
						<label for="wcp-orders-match-guests-by-email-{{data.index}}"><?php _e( 'Match guests by email', 'woo-conditional-payments' ); ?></label>
					</div>
				</div>

				<div class="value_input wcp_product_value_input">
					<select class="wc-product-search" multiple="multiple" name="wcp_conditions[{{data.index}}][product_ids][]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
						<# if ( data.selected_products && data.selected_products.length > 0 ) { #>
							<# _.each(data.selected_products, function(product) { #>
								<option value="{{ product['id'] }}" selected>{{ product['title'] }}</option>
							<# }) #>
						<# } #>
					</select>
				</div>

				<div class="value_input wcp_shipping_method_value_input">
					<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][shipping_method_ids][]" class="select" multiple>
						<?php foreach ( woo_conditional_payments_get_shipping_method_options() as $zone ) { ?>
							<optgroup label="<?php echo esc_attr( $zone['title'] ); ?>">
								<?php foreach ( $zone['methods'] as $method ) { ?>
									<option
										value="<?php echo $method['combined_id']; ?>"
										<# if ( data.shipping_method_ids && jQuery.inArray( '<?php echo $method['combined_id']; ?>', data.shipping_method_ids ) !== -1 ) { #>
											selected
										<# } #>
									>
										<?php echo $method['title']; ?>
									</option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_category_value_input">
					<select name="wcp_conditions[{{data.index}}][product_cat_ids][]" multiple class="select wc-enhanced-select">
						<?php foreach ( woo_conditional_payments_get_category_options() as $key => $label) { ?>
							<option value="<?php echo $key; ?>" <# if ( data.product_cat_ids && data.product_cat_ids.indexOf("<?php echo $key; ?>") !== -1 ) { #>selected<# } #>><?php echo $label; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_product_type_value_input">
					<select name="wcp_conditions[{{data.index}}][product_types][]" multiple class="select wc-enhanced-select">
						<?php foreach ( wcp_get_product_type_options() as $key => $label) { ?>
							<option value="<?php echo $key; ?>" <# if ( data.product_types && data.product_types.indexOf("<?php echo $key; ?>") !== -1 ) { #>selected<# } #>><?php echo $label; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_shipping_class_value_input">
					<select name="wcp_conditions[{{data.index}}][shipping_class_ids][]" multiple class="select wc-enhanced-select">
						<?php foreach ( woo_conditional_payments_get_shipping_class_options() as $key => $label ) { ?>
							<option value="<?php echo $key; ?>" <# if ( data.shipping_class_ids && data.shipping_class_ids.indexOf("<?php echo $key; ?>") !== -1 ) { #>selected<# } #>><?php echo $label; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_coupon_value_input">
					<select name="wcp_conditions[{{data.index}}][coupon_ids][]" multiple class="select wc-enhanced-select">
						<?php foreach ( woo_conditional_payments_get_coupon_options() as $key => $label ) { ?>
							<option value="<?php echo $key; ?>" <# if ( data.coupon_ids && data.coupon_ids.indexOf("<?php echo $key; ?>") !== -1 ) { #>selected<# } #>><?php echo $label; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_user_role_value_input">
					<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][user_roles][]" class="select" multiple>
						<?php foreach ( woo_conditional_payments_role_options() as $role_id => $name ) { ?>
							<option
								value="<?php echo $role_id; ?>"
								<# if ( data.user_roles && jQuery.inArray( '<?php echo $role_id; ?>', data.user_roles ) !== -1 ) { #>
									selected
								<# } #>
							>
								<?php echo $name; ?>
							</option>
						<?php } ?>
					</select>
				</div>

				<?php if ( defined( 'GROUPS_CORE_VERSION' ) ) { ?>
					<div class="value_input wcp_groups_value_input">
						<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][user_groups][]" class="select" multiple>
							<?php foreach ( woo_conditional_payments_groups_options() as $group_id => $name ) { ?>
								<option
									value="<?php echo $group_id; ?>"
									<# if ( data.user_groups && jQuery.inArray( '<?php echo $group_id; ?>', data.user_groups ) !== -1 ) { #>
										selected
									<# } #>
								>
									<?php echo $name; ?>
								</option>
							<?php } ?>
						</select>
					</div>
				<?php } ?>

				<?php if ( function_exists( 'pll_the_languages' ) ) { ?>
					<div class="value_input wcp_lang_polylang_value_input">
						<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][lang_polylang][]" class="select" multiple>
							<?php foreach ( woo_conditional_payments_polylang_options() as $lang_id => $lang ) { ?>
								<option
									value="<?php echo $lang_id; ?>"
									<# if ( data.lang_polylang && jQuery.inArray( '<?php echo $lang_id; ?>', data.lang_polylang ) !== -1 ) { #>
										selected
									<# } #>
								>
									<?php echo $lang; ?>
								</option>
							<?php } ?>
						</select>
					</div>
				<?php } ?>

				<?php if ( function_exists( 'icl_object_id' ) ) { ?>
					<div class="value_input wcp_lang_wpml_value_input">
						<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][lang_wpml][]" class="select" multiple>
							<?php foreach ( woo_conditional_payments_wpml_options() as $lang_id => $lang ) { ?>
								<option
									value="<?php echo $lang_id; ?>"
									<# if ( data.lang_wpml && jQuery.inArray( '<?php echo $lang_id; ?>', data.lang_wpml ) !== -1 ) { #>
										selected
									<# } #>
								>
									<?php echo $lang; ?>
								</option>
							<?php } ?>
						</select>
					</div>
				<?php } ?>

				<div class="value_input wcp_state_value_input">
					<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][states][]" class="select" multiple>
						<?php foreach ( woo_conditional_payments_state_options() as $country_id => $states ) { ?>
							<optgroup label="<?php echo esc_attr( $states['country'] ); ?>">
								<?php foreach ( $states['states'] as $state_id => $state ) { ?>
									<option
										value="<?php echo esc_attr( "{$country_id}:{$state_id}" ); ?>"
										<# if ( data.states && jQuery.inArray( '<?php echo esc_js( "{$country_id}:{$state_id}" ); ?>', data.states ) !== -1 ) { #>
											selected
										<# } #>
									>
										<?php echo $state; ?>
									</option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_country_value_input">
					<select class="wc-enhanced-select" name="wcp_conditions[{{data.index}}][countries][]" class="select" multiple>
						<?php foreach ( woo_conditional_payments_country_options() as $code => $country ) { ?>
							<option
								value="<?php echo $code; ?>"
								<# if ( data.countries && jQuery.inArray( '<?php echo $code; ?>', data.countries ) !== -1 ) { #>
									selected
								<# } #>
							>
								<?php echo $country; ?>
							</option>
						<?php } ?>
					</select>
				</div>

				<?php do_action( 'woo_conditional_payments_ruleset_value_inputs', $ruleset ); ?>
			</fieldset>
		</td>
	</tr>
</script>

<script type="text/html" id="tmpl-wcp_action_row_template">
	<tr valign="top" class="action_row">
		<th class="action_remove">
			<input type="checkbox" class="remove_action">
		</th>
		<th scope="row" class="titledesc">
			<fieldset>
				<select name="wcp_actions[{{data.index}}][type]" class="wcp_action_type_select">
					<?php foreach ( woo_conditional_payments_actions() as $key => $action ) { ?>
						<option
							value="<?php echo $key; ?>"
							<# if ( data.type == '<?php echo $key; ?>' ) { #>selected<# } #>
						>
							<?php echo $action['title']; ?>
						</option>
					<?php } ?>
				</select>
			</fieldset>
		</th>
		<td class="forminp wcp-payment-methods">
			<select name="wcp_actions[{{data.index}}][payment_method_ids][]" multiple class="select wc-enhanced-select" placeholder="<?php _e( 'Payment methods', 'woo-conditional-payments' ); ?>">
				<?php foreach ( woo_conditional_payments_get_payment_method_options() as $id => $method_title ) { ?>
					<option value="<?php echo $id; ?>" <# if ( data.payment_method_ids && data.payment_method_ids.indexOf("<?php echo $id; ?>") !== -1 ) { #>selected<# } #>><?php echo $method_title; ?></option>
				<?php } ?>
			</select>
		</td>
		<td class="forminp">
			<fieldset class="wcp_action_value_inputs">
				<div class="value_input wcp_price_value_input">
					<input name="wcp_actions[{{data.index}}][price]" type="number" step="0.01" value="{{ data.price }}" />
				</div>

				<div class="value_input wcp_fee_value_input">
					<input name="wcp_actions[{{data.index}}][fee_title]" type="text" value="{{ data.fee_title }}" placeholder="<?php _e( 'Fee description', 'woo-conditional-payments' ); ?>" />
					<div class="wcp-fee-amount-inputs">
						<input name="wcp_actions[{{data.index}}][fee_amount]" type="number" step="any" value="{{ data.fee_amount }}" placeholder="<?php _e( 'Amount', 'woo-conditional-payments' ); ?>" />
						<select name="wcp_actions[{{data.index}}][fee_mode]">
							<option value="fixed" <# if ( data.fee_mode === "fixed" ) { #>selected<# } #>><?php echo get_woocommerce_currency_symbol(); ?></option>
							<option value="pct" <# if ( data.fee_mode === "pct" ) { #>selected<# } #>>%</option>
						</select>
					</div>
					<select name="wcp_actions[{{data.index}}][fee_tax]">
						<?php foreach ( woo_conditional_payments_fee_tax_options() as $id => $label ) { ?>
							<option value="<?php echo $id; ?>" <# if ( data.fee_tax === "<?php echo $id; ?>" ) { #>selected<# } #>>
								<?php echo $label; ?>
							</option>
						<?php } ?>
					</select>
				</div>

				<div class="value_input wcp_error_msg_input">
					<textarea name="wcp_actions[{{data.index}}][error_msg]" rows="4" cols="40" placeholder="<?php esc_attr_e( __( 'Custom "no payment methods available" message', 'woo-conditional-payments' ) ); ?>">{{ data.error_msg }}</textarea>
				</div>
			</fieldset>
		</td>
	</tr>
</script>
