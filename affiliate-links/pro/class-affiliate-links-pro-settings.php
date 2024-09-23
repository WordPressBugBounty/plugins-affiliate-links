<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
require_once AFFILIATE_LINKS_PLUGIN_DIR . 'admin/class-affiliate-links-settings.php';

class Affiliate_Links_Pro_Settings {

	public function __construct() {
		$this->add_fields();
	}

	public function add_fields() {
		$options = array(
			array(
				'name'        => 'parameters_whitelist',
				'title'       => __( 'Parameters Whitelist', 'affiliate-links' ),
				'type'        => 'text',
				'tab'         => 'general',
				'default'     => '',
				'description' => __( 'URL parameters which should be passed to the target URL (comma separated)', 'affiliate-links' ),
			),
		);
		foreach ( $options as $field ) {
			Affiliate_Links_Settings::add_field( $field );
		}
	}
}