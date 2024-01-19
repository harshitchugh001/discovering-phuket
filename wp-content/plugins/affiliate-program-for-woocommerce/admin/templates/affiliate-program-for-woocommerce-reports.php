<?php
/**
 * This template will responsible for admin detailed reports
 *
 * @link       https://eplugins.in/
 * @since      1.0.0
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/public
 */

 $reports = get_user_meta( $user_log_id, 'reports', true );
?>
 <div class="apf_goback">
	 <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-reports&tab=apf_admin_reports' ) ); ?>"> 
	  <?php esc_html_e( 'Go back', 'affiliate-program-for-woocommerce' ); ?>
	 </a>
 </div>
 <table id="apf_datatable" class="eplugin-table" style="width:100%">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'affiliate-program-for-woocommerce' ); ?></th>
				 <th><?php esc_html_e( 'Order Id', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Order Total', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Activity', 'affiliate-program-for-woocommerce' ); ?></th>
			   <th><?php esc_html_e( 'Date', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Earnings', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Total Balance', 'affiliate-program-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
		   <?php if ( ! empty( $reports ) ) : ?>
			  
				 <?php foreach ( array_reverse( $reports ) as $key => $value ) : ?>
				 <tr>
					<td><?php echo ( ! empty( $value['customer_name'] ) ) ? esc_html( $value['customer_name'] ) : ''; ?></td>
					 <td>
						<?php
						if ( ! empty( $value['order_id'] ) ) {
							$apf_order = wc_get_order( $value['order_id'] );
							?>
							 <a href="<?php echo esc_url( get_edit_post_link( $value['order_id'] ) ); ?>">
							<?php echo esc_html( $apf_order->get_order_number() ); ?></a>
							<?php
						} else {
							echo '-';
						}

						?>
					 </td>
					<td><?php echo ( ! empty( $value['order_total'] ) ) ? esc_html( $value['order_total'] ) : '-'; ?></td>
					<td><?php echo ( ! empty( $value['customer_event'] ) ) ? esc_html( $value['customer_event'] ) : '-'; ?></td>
					<td><?php echo ( ! empty( $value['date'] ) ) ? esc_html( $value['date'] ) : '-'; ?></td>
					<td><?php echo ( ! empty( $value['earnings'] ) ) ? esc_html( $value['earnings'] ) : '-'; ?></td>
					<td><?php echo ( ! empty( $value['total_balance'] ) ) ? esc_html( $value['total_balance'] ) : '-'; ?></td>
				</tr>
				 <?php endforeach; ?>
		   <?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
			  <th><?php esc_html_e( 'Customer Name', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Order Id', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Order Total', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Customer Event', 'affiliate-program-for-woocommerce' ); ?></th>
			   <th><?php esc_html_e( 'Date', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Earnings', 'affiliate-program-for-woocommerce' ); ?></th>
				 <th><?php esc_html_e( 'Total Balance', 'affiliate-program-for-woocommerce' ); ?></th>
			</tr>
		</tfoot>
	</table>
