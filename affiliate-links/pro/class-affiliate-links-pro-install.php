<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * The Affiliate Links Core Install
 */
class Affiliate_Links_Install {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'install' ), 5 );
	}

	public static function install() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		self::create_tables( $wpdb );
	}

	public static function create_tables( $wpdb ) {
		dbDelta( self::get_schema( $wpdb ) );
	}

	public static function get_schema( $wpdb ) {
		$charset_collate = $wpdb->get_charset_collate();

		return "
      CREATE TABLE {$wpdb->prefix}af_links_activity (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        link_id text NOT NULL,
        browser text NOT NULL,
        referer text NOT NULL,
        os text NOT NULL,
        platform text NOT NULL,
        lang text NOT NULL,
        created_date datetime NOT NULL,
        created_date_gmt datetime NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";
	}
}

Affiliate_Links_install::init();