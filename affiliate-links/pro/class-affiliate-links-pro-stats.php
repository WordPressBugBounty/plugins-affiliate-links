<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
include_once AFFILIATE_LINKS_PRO_PLUGIN_DIR . '/' . 'class-affiliate-links-pro-base.php';

/**
 * The Search Terms Class.
 */
class Affiliate_Links_Pro_Stats extends Affiliate_Links_Pro_Base {

	private static $instance;
	public $chart_data = array();
	/**
	 * @var Affiliate_Links_Pro_Report_Table
	 */
	protected $table;
	protected $colorGenerator;

	protected function __construct() {
		parent::__construct();

		add_action( 'af_link_before_redirect', array(
			$this,
			'update_activity',
		) );
		add_action( 'af_link_before_iframe', array(
			$this,
			'update_activity',
		) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
			add_action( 'current_screen', array( $this, 'is_screen' ) );
			add_action( 'af_link_report_tab_' . $this->get_current_tab(), array(
				$this,
				'render_view',
			) );
			add_action( 'af_link_report_tab_' . $this->get_current_tab(), array(
				$this,
				'delete_stats',
			) );
		}
	}

	public function get_current_tab() {
		return $this->get_request_var( 'tab', 'links-by-date' );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function localize_script() {
		wp_localize_script( 'affiliate-links-pro', 'af_links', array( 'af_links_data' => $this->chart_data ) );
	}

	public function link_by_date_get_data() {
		$range = $this->get_date_by_range();

		if ( count( $range ) && $this->get_request_var( 'link_id' ) ) {
			$where = 'link_id=' . $this->get_request_var( 'link_id' )
			         . " AND created_date >= '{$range['start_date']}'"
			         . " AND created_date <= '{$range['end_date']}'";

			$args = array(
				'SELECT'   => '*, count(created_date) as hits',
				'WHERE'    => $where,
				"GROUP BY" => 'day(created_date)',
			);

			$this->get_report_data( $args, $range );
		}
	}

	public function get_date_by_range() {
		$data = array();

		switch ( TRUE ) {
			case $this->get_current_range() == 'last_month':
				$data['start_date'] = date( 'Y-m-d', strtotime( 'first day of previous month' ) );
				$data['end_date']   = date( 'Y-m-d', strtotime( 'first day of this month' ) );
				break;
			case $this->get_current_range() == 'month':
				$data['start_date'] = date( 'Y-m-d', strtotime( 'first day of this month' ) );
				$data['end_date']   = current_time( 'mysql' );
				break;
			case $this->get_current_range() == 'week':
				$data['start_date'] = date( 'Y-m-d', strtotime( ' -1 week' ) );
				$data['end_date']   = current_time( 'mysql' );
				break;
			case $this->get_current_range() == 'day':
				$data['start_date'] = date( 'Y-m-d', strtotime( ' -1 day' ) );
				$data['end_date']   = current_time( 'mysql' );
				break;
			case ( $this->get_current_range() == 'custom' && $this->get_request_var( 'start_date' ) && $this->get_request_var( 'end_date' ) ):
				$data['start_date'] = $this->get_request_var( 'start_date' ) . ' 00:00:00';
				$data['end_date']   = $this->get_request_var( 'end_date' ) . ' 23:59:59';
				break;
		}

		return $data;
	}

	public function get_current_range() {
		return $this->get_request_var( 'range', 'week' );
	}

	protected function get_report_data( $args, $range ) {
		if ( ! empty( $args ) ) {

			$temp   = array();
			$items  = $this->load_activity( $args, ARRAY_A );
			$period = array();

			$begin = new DateTime( $range['start_date'] );
			$end   = new DateTime( date( "Y-m-d", strtotime( "+1 day", strtotime( $range['end_date'] ) ) ) );
			while ( $begin < $end ) {
				$period[] = $begin->format( 'Y-m-d' );
				$begin->modify( '+1 day' );
			}

			foreach ( $items as $item ) {
				$date                      = date( 'Y-m-d', strtotime( $item['created_date'] ) );
				$this->chart_data[ $date ] = array( $date, $item['hits'] );
			}

			foreach ( $period as $date ) {
				$temp[ $date ] = array( $date, 0 );
			}

			$this->chart_data = array_values( wp_parse_args( $this->chart_data, $temp ) );
		}
	}

	public function load_activity( $statements = array(), $output = OBJECT ) {
		$statements = wp_parse_args( $statements, $this->get_default_statements() );
		$statements = apply_filters( 'af_link_load_activity_statements', $statements );
		$expression = '';

		foreach ( $statements as $statement => $value ) {
			if ( ! $value ) {
				continue;
			}
			$expression .= "$statement $value ";
		}

		do_action( 'af_link_load_activity_expression', $expression );

		return $this->wpdb->get_results( $expression, $output );
	}

	private function get_default_statements() {
		return array(
			'SELECT'   => '*',
			'FROM'     => self::get_table(),
			'JOIN'     => '',
			'WHERE'    => '',
			'GROUP BY' => '',
			'ORDER BY' => 'created_date_gmt DESC',
			'LIMIT'    => '',

		);
	}

	public static function get_table() {
		global $wpdb;

		return "{$wpdb->prefix}af_links_activity";
	}

	public function link_cat_by_date_get_data() {
		$range = $this->get_date_by_range();

		if ( count( $range ) && $this->get_request_var( 'link_cat_id' ) ) {
			$link_ids = new WP_Query( array(
				'post_type' => 'affiliate-links',
				'fields'    => 'ids',
				'tax_query' => array(
					array(
						'taxonomy' => 'affiliate-links-cat',
						'field'    => 'id',
						'terms'    => $this->get_request_var( 'link_cat_id' ),
					),
				),
			) );
			$link_ids = implode( ',', $link_ids->get_posts() );
			$where    = "link_id IN ($link_ids)"
			            . " AND created_date >= '{$range['start_date']}'"
			            . " AND created_date <= '{$range['end_date']}'";

			$args = array(
				'SELECT'   => '*, count(created_date) as hits',
				'WHERE'    => $where,
				"GROUP BY" => 'day(created_date)',
			);

			$this->get_report_data( $args, $range );
		}
	}

	public function is_screen() {
		$this->table = new Affiliate_Links_Pro_Report_Table( array(
			'singular'       => 'link',
			//singular name of the listed records
			'plural'         => 'links',
			//plural name of the listed records
			'ajax'           => FALSE,
			'stats_instance' => $this,
		) );
	}

	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=affiliate-links',
			__( 'Affiliate Links Reports', 'affiliate-links' ),
			__( 'Reports', 'affiliate-links' ),
			'manage_options',
			'reports',
			array( $this, 'render_reports' )
		);
	}

	public function render_reports() {
		$this->render_view( 'admin-reports' );
	}

	public function delete_stats() {
		if( current_user_can( 'manage_options' ) ) {
			if ( isset( $_GET['af_delete_nonce'] ) AND wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['af_delete_nonce'] ) ), 'delete_stats' ) ) {
				$adminurl = strtolower( admin_url() );
				$referer = strtolower( wp_get_referer() );

				if( strpos( $referer, $adminurl ) !== false ) {
					global $wpdb;
					$wpdb->query("TRUNCATE TABLE " . self::get_table());

					if ( $wpdb->last_error ) {
						print $wpdb->last_error;
					} else {
						esc_html_e( 'All stats data was deleted', 'affiliate-links' );
					}
				}
			}
		}
	}

	public function get_links() {
		$args  = array(
			'post_type'      => 'affiliate-links',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
		);
		$links = new WP_Query( $args );

		return $links->get_posts();
	}

	public function get_links_cats() {
		return get_terms( 'affiliate-links-cat' );
	}

	public function get_setting_tabs() {
		return array(
			'links-by-date'    => __( 'Links by Date', 'affiliate-links' ),
			'browser-by-date'  => __( 'Browser by Date', 'affiliate-links' ),
			'link-by-date'     => __( 'Link by Date', 'affiliate-links' ),
			'link-cat-by-date' => __( 'Link Category by Date', 'affiliate-links' ),
		);
	}

	public function get_ranges() {
		return array(
			'last_month' => __( 'Last Month', 'affiliate-links' ),
			'month'      => __( 'This Month', 'affiliate-links' ),
			'week'       => __( 'Last 7 Days', 'affiliate-links' ),
			//'day'          => __( 'Last Day' )
		);
	}

	public function get_link_hints( $id ) {
		return get_post_meta( $id, '_affiliate_links_stat', TRUE );
	}

	public function update_activity( $post_id ) {
		$item                     = $this->current_link;
		$item['created_date']     = current_time( 'mysql' );
		$item['created_date_gmt'] = current_time( 'mysql', 1 );
		$item['link_id']          = $post_id;
		$item['referer']          = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : __( 'Direct Entry', 'affiliate-links' );

		$this->wpdb->insert( self::get_table(), $item );
	}

	public function get_links_data( $args = array() ) {
		$data  = array();
		$range = $this->get_date_by_range();

		if ( ! count( $range ) ) {
			return $data;
		}

		$args['SELECT']   = "*, {$this->wpdb->prefix}posts.post_title as title, count(link_id) as hits";
		$args['WHERE']    = "created_date >= '{$range['start_date']}' AND created_date <= '{$range['end_date']}'";
		$args['GROUP BY'] = 'link_id';
		$args['JOIN']     = "{$this->wpdb->prefix}posts on {$this->wpdb->prefix}posts.ID = {$this->wpdb->prefix}af_links_activity.link_id";

		if ( $this->get_request_var( 'orderby' ) && $this->get_request_var( 'order' ) ) {
			$args['ORDER BY'] = $this->get_request_var( 'orderby' ) . ' ' . $this->get_request_var( 'order' );
		}

		$items  = $this->load_activity( $args, ARRAY_A );
		$colors = $this->get_colors( count( $items ) );

		foreach ( $items as $key => $item ) {
			$data[ $key ]['id']     = $item['id'];
			$data[ $key ]['title']  = get_the_title( $item['link_id'] );
			$data[ $key ]['hits']   = $item['hits'];
			$data[ $key ]['legend'] = $colors[ $key ];
		}

		$this->chart_data = $data;

		return $data;
	}

	public function get_colors( $count ) {
		if ( FALSE === ( $colors = get_transient( 'link_colors_' . $this->get_current_range() ) )
		     || ! count( $colors )
		     || count( $colors ) !== $count
		) {
			$colors = RandomColor::many( $count );
			set_transient( 'link_colors_' . $this->get_current_range(), $colors, 12 * HOUR_IN_SECONDS );
		}

		return $colors;
	}

	public function get_browser_data( $args = array() ) {
		$data  = array();
		$range = $this->get_date_by_range();

		if ( ! count( $range ) ) {
			return $data;
		}

		$args['SELECT']   = '*, count(link_id) as hits';
		$args['WHERE']    = "created_date >= '{$range['start_date']}' AND created_date <= '{$range['end_date']}'";
		$args['GROUP BY'] = 'browser';

		if ( $this->get_request_var( 'orderby' ) && $this->get_request_var( 'order' ) ) {
			$args['ORDER BY'] = $this->get_request_var( 'orderby' ) . ' ' . $this->get_request_var( 'order' );
		}

		$items  = $this->load_activity( $args, ARRAY_A );
		$colors = $this->get_colors( count( $items ) );

		foreach ( $items as $key => $item ) {
			$data[ $key ] = array(
				'id'      => $item['id'],
				'browser' => AFL_PRO()->get_browser_title( $item['browser'] ),
				'hits'    => $item['hits'],
				'legend'  => $colors[ $key ],
			);
		}

		$this->chart_data = $data;

		return $data;
	}

	public function get_link_browsers_data() {
		$data    = array();
		$range   = $this->get_date_by_range();
		$link_id = $this->get_request_var( 'link_id' );

		if ( ! $link_id ) {
			return $data;
		}

		return $this->load_activity( array(
			'SELECT'   => 'browser as name, count(browser) as hits',
			'WHERE'    => "link_id='{$link_id}' AND created_date >= '{$range['start_date']}' AND created_date <= '{$range['end_date']}'",
			'GROUP BY' => 'browser',
		), ARRAY_A );
	}

	public function get_link_data( $field ) {
		$data    = array();
		$range   = $this->get_date_by_range();
		$link_id = $this->get_request_var( 'link_id' );

		if ( ! $link_id ) {
			return $data;
		}

		return $this->load_activity( array(
			'SELECT'   => "$field, count({$field}) as hits",
			'WHERE'    => "link_id='{$link_id}' AND created_date >= '{$range['start_date']}' AND created_date <= '{$range['end_date']}'",
			'GROUP BY' => $field,
		), ARRAY_A );
	}

	public function return_ids( $item ) {
		return $item['link_id'];
	}

	public function get_popular_links( $args ) {
		$available_ids = array();

		$links_args = array(
			'SELECT'   => 'link_id, count(link_id) as hits',
			'GROUP BY' => 'link_id',
			'ORDER BY' => 'hits DESC',
		);

		$links_ids = $this->load_activity( $links_args, ARRAY_A );
		$links_ids = $this->get_link_ids( $links_ids );

		$query_args = array(
			'post_type'     => Affiliate_Links::$post_type,
			'no_found_rows' => TRUE,
			'post_status'   => 'publish',
			'post__in'      => $links_ids,
			'fields'        => 'ids',
		);

		// check widget cats
		if ( isset( $args['cat'] ) && $args['cat'] != '0' ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => Affiliate_Links::$taxonomy,
					'field'    => 'term_id',
					'terms'    => $args['cat'],
				),
			);
		}

		//check shortcode cats
		if ( isset( $args['category'] ) && $args['category'] != '0' ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => Affiliate_Links::$taxonomy,
					'field'    => 'slug',
					'terms'    => $args['category'],
				),
			);
		}

		$slice_number = isset( $args['number'] ) ? $args['number'] : 5;

		$r = new WP_Query( $query_args );

		$available_ids_by_cat = $r->get_posts();

		if ( ! empty( $available_ids_by_cat ) ) {
			$available_ids = array_intersect( $links_ids, $available_ids_by_cat );
			$available_ids = array_slice( $available_ids, 0, $slice_number );
		}

		return $available_ids;
	}

	public function get_link_ids( $data ) {
		$ids = array_map( array( $this, 'return_ids' ), $data );

		return $ids;
	}

	public function get_recent_links( $args ) {
		$available_ids = array();
		$links_args    = array(
			'SELECT'   => 'link_id, MAX(created_date_gmt) as date',
			'GROUP BY' => 'link_id',
			'ORDER BY' => 'date DESC',
		);

		$links_ids = $this->load_activity( $links_args, ARRAY_A );
		$links_ids = $this->get_link_ids( $links_ids );

		$query_args = array(
			'post_type'     => Affiliate_Links::$post_type,
			'no_found_rows' => TRUE,
			'post_status'   => 'publish',
			'post__in'      => $links_ids,
			'fields'        => 'ids',
		);

		if ( isset( $args['cat'] ) && $args['cat'] != '0' ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => Affiliate_Links::$taxonomy,
					'field'    => 'term_id',
					'terms'    => $args['cat'],
				),
			);
		}

		if ( isset( $args['category'] ) && $args['category'] != '0' ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => Affiliate_Links::$taxonomy,
					'field'    => 'term_slug',
					'terms'    => $args['category'],
				),
			);
		}

		$slice_number = isset( $args['number'] ) ? $args['number'] : 5;

		$r = new WP_Query( $query_args );

		$available_ids_by_cat = $r->get_posts();

		if ( ! empty( $available_ids_by_cat ) ) {
			$available_ids = array_intersect( $links_ids, $available_ids_by_cat );
			$available_ids = array_slice( $available_ids, 0, $slice_number );
		}

		return $available_ids;
	}
}