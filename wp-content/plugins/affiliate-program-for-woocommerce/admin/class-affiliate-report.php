<?php
/**
 * This template will responsible for admin reports
 *
 * @link       https://eplugins.in/
 * @since      1.0.0
 *
 * @package    Affiliate_Program_For_Woocommerce
 * @subpackage Affiliate_Program_For_Woocommerce/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Affiliate_Report.
 *
 * @package     Affiliate_Program_For_Woocommerce
 * @version     1.0.0
 */
class Affiliate_Report extends WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 *
	 * @name prepare_items
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$perpage = 10;
		$currentpage = $this->get_pagenum();

		$data = $this->table_data( $perpage, $currentpage );
		usort( $data, array( &$this, 'sort_data' ) );

		$totalitems = $this->apf_total_users_count();

		$this->set_pagination_args(
			array(
				'total_items' => $totalitems,
				'per_page'    => $perpage,
			)
		);

		$data = array_slice( $data, ( ( $currentpage - 1 ) * $perpage ), $perpage );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $data;
	}

	/**
	 * Total users of the users
	 *
	 * @name apf_total_users_count
	 * @since 1.0.0
	 */
	public function apf_total_users_count() {
		$result = count_users();

		$total_count = 0;

		$roles = get_option( 'affiliate_select_role', false );

		if ( empty( $roles ) ) {
			return $result['total_users'];
		}

		foreach ( $result['avail_roles'] as $role => $count ) {
			if ( ! empty( $roles ) && in_array( $role, $roles ) ) {
				$total_count += $count;
			}
		}

		return $total_count;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @name get_columns
	 * @since 1.0.0
	 * @return Array
	 */
	public function get_columns() {
		 $columns = array(
			 'user_name'         => esc_html__( 'User Name', 'affiliate-program-for-woocommerce' ),
			 'user_email'        => esc_html__( 'User Email', 'affiliate-program-for-woocommerce' ),
			 'referred_users'    => esc_html__( 'Total Referred', 'affiliate-program-for-woocommerce' ),
			 'total_earning'     => esc_html__( 'Total Earning', 'affiliate-program-for-woocommerce' ),
			 'total_balance'     => esc_html__( 'Current balance', 'affiliate-program-for-woocommerce' ),
			 'action'            => esc_html__( 'Action', 'affiliate-program-for-woocommerce' ),
		 );

		 return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @name get_hidden_columns
	 * @since 1.0.0
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0.0
	 * @name get_sortable_columns
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array(
			'user_name' => array( 'user_name', false ),
			'user_email' => array( 'user_email', false ),
		);
	}

	/**
	 * Get the table data
	 *
	 * @name table_data
	 * @since 1.0.0
	 * @param int $per_page Per page.
	 * @param int $current_page Current page.
	 * @return Array
	 */
	private function table_data( $per_page, $current_page ) {
		$data = array();

		$args['number'] = $per_page;
		$args['offset'] = ( $current_page - 1 ) * $per_page;

		$roles = get_option( 'affiliate_select_role', false );
		if ( ! empty( $roles ) ) {

			$args['role__in'] = $roles;
		}

		$user_data        = new WP_User_Query( $args );
		$user_data        = $user_data->get_results();

		foreach ( $user_data as $key => $value ) {

			$apf_total_earn   = get_user_meta( $value->data->ID, 'apf_total_earn', true );
			$apf_total_earn   = ( empty( $apf_total_earn ) ) ? 0 : $apf_total_earn;

			// Total balance.
			$apf_total_balance   = get_user_meta( $value->data->ID, 'apf_total_balance', true );
			$apf_total_balance   = ( empty( $apf_total_balance ) ) ? 0 : $apf_total_balance;

			// Total referred.
			$apf_total_referred   = get_user_meta( $value->data->ID, 'apf_total_referred', true );
			$apf_total_referred   = ( empty( $apf_total_referred ) ) ? 0 : $apf_total_referred;

			$data[] = array(
				'id' => $value->data->ID,
				'user_name'          => $value->data->user_login,
				'user_email'       => $value->data->user_email,
				'referred_users'  => $apf_total_referred,
				'total_earning'   => $apf_total_earn,
				'total_balance' => $apf_total_balance,
			);
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array  $item        Data.
	 * @param  String $column_name - Current column name.
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'user_name':
				$actions = array(
					'view_point_log' => '<a href="' . admin_url( 'admin.php?page=wc-reports&tab=apf_admin_reports&user_id =' . $item['id'] ) . ' " >' . esc_html__( 'View Details', 'affiliate-program-for-woocommerce' ) . '</a>',

				);
				return $item[ $column_name ] . $this->row_actions( $actions );
			case 'user_email':
				return $item[ $column_name ];
			case 'referred_users':
				return $item[ $column_name ];
			case 'total_earning':
				return $item[ $column_name ];
			case 'total_balance':
				return $item[ $column_name ];
			case 'action':
				return $this->apf_view_button( $item['id'] );
			default:
				return false;
		}
	}

	/**
	 * Function will add the new button in the table
	 *
	 * @since 1.0.0
	 * @name apf_view_button.
	 * @param int $user_id Id of the user that will be updated.
	 */
	public function apf_view_button( $user_id ) {
		$button = '<a class="apf_update button button-primary "href="' . admin_url( 'admin.php?page=wc-reports&tab=apf_admin_reports&user_id =' . $user_id ) . ' " >' . esc_html__( 'View Details', 'affiliate-program-for-woocommerce' ) . '</a>';
		return $button;
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @param  int $a     first value.
	 * @param int $b     second value.
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {
		// Set defaults.
		$orderby = 'user_name';
		$order = 'asc';

		// If orderby is set, use this as the sort column.
		if ( ! empty( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) ) {
			$orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		}

		// If order is set use this as the order.
		if ( ! empty( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ) {
			$order = sanitize_text_field( wp_unslash( $_GET['order'] ) );
		}

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		if ( 'asc' === $order ) {
			return $result;
		}

		return -$result;
	}
}
	$affiliate_list_table = new Affiliate_Report();
	$affiliate_list_table->prepare_items();
if ( isset( $_GET['user_id_'] ) ) {
	$user_log_id = sanitize_text_field( wp_unslash( $_GET['user_id_'] ) );
	require_once plugin_dir_path( __FILE__ ) . 'templates/affiliate-program-for-woocommerce-reports.php';
} else {
	?>
			<div class="wrap eplugins-table-wrap">
				<div id="icon-users" class="icon32"></div>
				<h2></h2>
			<?php $affiliate_list_table->display(); ?>
			</div>
		<?php
}
