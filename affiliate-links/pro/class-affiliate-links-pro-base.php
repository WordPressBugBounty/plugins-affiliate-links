<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * The Affiliate Links Core Plugin Class.
 */
abstract class Affiliate_Links_Pro_Base {

	/**
	 * @var wpdb
	 */
	protected $wpdb;

	private $data = array();

	protected function __construct() {
		$this->wpdb = $this->get_wpdb();
	}

	protected function get_wpdb() {
		return $GLOBALS['wpdb'];
	}

	public function get_request_var( $var, $default = "" ) {
		return isset( $_REQUEST[ $var ] ) ? sanitize_text_field( $_REQUEST[ $var ] ) : $default;
	}

	public function __get( $name ) {
		if ( array_key_exists( $name, $this->data ) ) {
			return $this->data[ $name ];
		}
	}

	public function __set( $name, $value ) {
		$this->data[ $name ] = $value;
	}

	public function render_view( $slug, $require_once = TRUE ) {
		$tab_data_func_name = str_replace( '-', '_', $slug ) . '_get_data';
		if ( method_exists( $this, $tab_data_func_name ) ) {
			call_user_func( array( $this, $tab_data_func_name ) );
		}
		if ( method_exists( $this, 'localize_script' ) ) {
			add_action( 'admin_footer', array( $this, 'localize_script' ) );
		}
		$this->load_template( AFFILIATE_LINKS_PRO_PLUGIN_DIR . "/views/html-{$slug}.php", $require_once );
	}

	public function load_template( $_template_file, $require_once = TRUE ) {
		if ( ! file_exists( $_template_file ) ) {
			return '';
		}
		if ( $require_once ) {
			require_once( $_template_file );
		} else {
			require( $_template_file );
		}
	}
}