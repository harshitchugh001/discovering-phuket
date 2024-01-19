<div id="wcs-debug">
	<div id="wcs-debug-header">
		<div class="wcs-debug-title"><?php _e( 'Conditional Shipping Debug', 'woo-conditional-shipping' ); ?></div>
		<div class="wcs-debug-toggle"></div>
	</div>

	<div id="wcs-debug-contents">
		<?php if ( $debug['shipping_zone'] ) { ?>
			<h3><?php _e( 'Shipping zone', 'woo-conditional-shipping' ); ?></h3>

			<p><?php _e( 'Matched shipping zone: ', 'woo-conditional-shipping' ); ?><?php echo $debug['shipping_zone']['name_with_url']; ?></p>
			<p class="wcs-debug-tip"><?php _e( "WooCommerce will find the first matching zone and skip the rest. Make sure you don't have duplicate zones for the same region.", 'woo-conditional-shipping' ); ?></p>
		<?php } ?>

		<h3><?php _e( 'Shipping methods', 'woo-conditional-shipping' ); ?></h3>

		<table class="wcs-debug-table wcs-debug-table-fixed">
			<thead>
				<tr>
					<th>
						<?php _e( 'Before filtering', 'woo-conditional-shipping' ); ?>
					</th>
					<th>
						<?php _e( 'After filtering', 'woo-conditional-shipping' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo implode( '<br>', $debug['shipping_methods']['before'] ); ?>
						<?php if ( empty( $debug['shipping_methods']['after'] ) ) { ?>
							<em><?php _e( 'No shipping methods', 'woo-conditional-shipping' ); ?></em>
						<?php } ?>
					</td>
					<td>
						<?php echo implode( '<br>', $debug['shipping_methods']['after'] ); ?>
						<?php if ( empty( $debug['shipping_methods']['after'] ) ) { ?>
							<em><?php _e( 'No shipping methods', 'woo-conditional-shipping' ); ?></em>
						<?php } ?>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="wcs-debug-tip"><?php _e( "If shipping method is not listed above or is not available as expected, another plugin might be affecting its visibility or its settings do not allow it to be available for the cart or customer address.", 'woo-conditional-shipping' ); ?></p>

		<h3><?php _e( 'Rulesets', 'woo-conditional-shipping' ); ?></h3>

		<?php if ( empty( $debug['rulesets'] ) ) { ?>
			<p><?php _e( 'No rulesets were run.', 'woo-conditional-shipping' ); ?></p>
		<?php } ?>

		<?php foreach ( $debug['rulesets'] as $ruleset_id => $data ) { ?>
			<div class="wcs-debug-<?php echo $ruleset_id; ?>">
				<h3 class="ruleset-title">
					<a href="<?php echo wcs_get_ruleset_admin_url( $data['ruleset_id'] ); ?>" target="_blank">
						<?php echo $data['ruleset_title']; ?>
					</a>
				</h3>

				<table class="wcs-debug-table wcs-debug-conditions">
					<thead>
						<tr>
							<th colspan="2"><?php _e( 'Conditions', 'woo-conditional-shipping' ); ?> - <?php echo wcs_get_ruleset_operator_label( $data['ruleset_id'] ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $data['conditions'] as $condition ) { ?>
							<tr class="result-<?php echo ( $condition['result'] ? 'fail' : 'pass' ); ?>">
								<td><?php echo $condition['desc']; ?></td>
								<td class="align-right"><?php echo ( $condition['result'] ? __( 'Fail', 'woo-conditional-shipping' ) : __( 'Pass', 'woo-conditional-shipping' ) ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr class="result-<?php echo ( $data['result'] ? 'pass' : 'fail' ); ?>">
							<th colspan="2" class="align-right"><?php echo ( $data['result'] ? __( 'Pass', 'woo-conditional-shipping' ) : __( 'Fail', 'woo-conditional-shipping' ) ); ?></th>
						</tr>
					</tfoot>
				</table>

				<table class="wcs-debug-table wcs-debug-actions">
					<thead>
						<tr>
							<th><?php _e( 'Actions', 'woo-conditional-shipping' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $data['actions'] as $action ) { ?>
							<tr class="status-<?php echo $action['status']; ?>">
								<td>
									<?php echo implode( ' - ', $action['cols'] ); ?>

									<?php if ( $action['desc'] ) { ?>
										<br><small><?php echo $action['desc']; ?></small>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
						<?php if ( empty( $data['actions'] ) ) { ?>
							<tr>
								<td><?php _e( 'No actions were run for this ruleset', 'woo-conditional-shipping' ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		<?php } ?>
	</div>
</div>
