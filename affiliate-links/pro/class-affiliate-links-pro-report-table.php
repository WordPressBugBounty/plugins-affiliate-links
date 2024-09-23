<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Affiliate_Links_Pro_Report_Table extends WP_List_Table {

	const ITEMS_PER_PAGE = 7;

	/**
	 * @var Affiliate_Links_Pro_Stats
	 */
	protected $stats_instance;

	protected $columns = array();

	protected $sortable_columns = array();

	protected $table_data = array();

	function __construct( $args = array() ) {
		global $status, $page;
		$this->stats_instance = $args['stats_instance'];
		parent::__construct( $args );

	}

	function column_legend( $item ) {
		return sprintf( '<p style="background-color:%s; width: 20px">&nbsp</p>', $item['legend'] );
	}

	function column_default( $item, $column_name ) {

		return $item[ $column_name ];

	}

	public function single_row( $item ) {
		echo '<tr>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	function prepare_items() {

		$per_page              = self::ITEMS_PER_PAGE;
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
		$current_page          = $this->get_pagenum();
		$data                  = $this->get_table_data();
		$total_items           = count( $data );
		$data                  = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items           = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			//WE have to calculate the total number of items
			'per_page'    => $per_page,
			//WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page )
			//WE have to calculate the total number of pages
		) );
	}

	public function get_columns() {
		return $this->columns;
	}

	public function set_columns( $columns ) {
		if ( is_array( $columns ) ) {
			$this->columns = $columns;
		}

		return $this;
	}

	protected function get_sortable_columns() {
		return $this->sortable_columns;
	}

	public function set_sortable_columns( $columns ) {
		if ( is_array( $columns ) ) {
			$this->sortable_columns = $columns;
		}

		return $this;
	}

	protected function get_table_data() {
		return $this->table_data;
	}

	public function set_table_data( $data ) {
		if ( is_array( $data ) ) {
			$this->table_data = $data;
		}

		return $this;
	}
}