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
				'title'       => 'Parameters Whitelist',
				'title_i18n'  => true,
				'type'        => 'text',
				'tab'         => 'general',
				'default'     => '',
				'description' => 'URL parameters which should be passed to the target URL (comma separated)',
				'description_i18n' => true,
			),
		);
		foreach ( $options as $field ) {
			Affiliate_Links_Settings::add_field( $field );
		}
	}
}