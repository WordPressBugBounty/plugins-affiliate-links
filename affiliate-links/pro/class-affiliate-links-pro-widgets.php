<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Affiliate_Links_Pro_Widgets {

	public function __construct() {
		add_action( 'widgets_init', array( $this, 'load_widgets' ) );
	}

	public function load_widgets() {
		$available_widgets = $this->get_widgets();
		foreach ( $available_widgets as $class => $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
				register_widget( $class );
			}
		}
	}

	public function get_widgets() {
		return array(
			'Affiliate_Links_Pro_Recent_Links'  => $this->get_widget_class_name( 'recent-links' ),
			'Affiliate_Links_Pro_Popular_Links' => $this->get_widget_class_name( 'popular-links' ),
		);
	}

	protected function get_widget_class_name( $slug ) {
		return AFFILIATE_LINKS_PRO_PLUGIN_DIR . '/widgets/' . 'class-affiliate-links-widget-' . $slug . '.php';
	}

}