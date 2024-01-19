<?php
/**
 * This template will responsible for frontend reports
 *
 * @link       https://eplugins.in/
 * @since      1.0.0
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/public
 */

 $user_id         = get_current_user_ID();
 $reports = get_user_meta( $user_id, 'reports', true );
?>
 <div class="apf_goback">
	 <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>"> 
	  <?php esc_html_e( 'Go back', 'affiliate-program-for-woocommerce' ); ?>
	 </a>
 </div>
 <table id="apf_datatable" class="table table-striped table-bordered" style="width:100%">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'affiliate-program-for-woocommerce' ); ?></th>
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
					<td><?php echo ( ! empty( $value['customer_name'] ) ) ? esc_html( $value['customer_name'] ) : '-'; ?></td>
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
				<th><?php esc_html_e( 'Order Total', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Customer Event', 'affiliate-program-for-woocommerce' ); ?></th>
			   <th><?php esc_html_e( 'Date', 'affiliate-program-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Earnings', 'affiliate-program-for-woocommerce' ); ?></th>
				 <th><?php esc_html_e( 'Total Balance', 'affiliate-program-for-woocommerce' ); ?></th>
			</tr>
		</tfoot>
	</table>
