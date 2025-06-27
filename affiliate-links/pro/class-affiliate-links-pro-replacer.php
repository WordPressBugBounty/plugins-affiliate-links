<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
include_once AFFILIATE_LINKS_PRO_PLUGIN_DIR . '/' . 'class-affiliate-links-pro-base.php';

// Include pluggable.php to ensure wp_get_current_user() is available
if ( ! function_exists( 'wp_get_current_user' ) ) {
	require_once ABSPATH . 'wp-includes/pluggable.php';
}

class Affiliate_Links_Pro_Replacer extends Affiliate_Links_Pro_Base {

	public $template = 'link-replacer';

	public $messages = array();

	public function __construct() {
		parent::__construct();

		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
		}
	}

	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=affiliate-links',
			__( 'Affiliate Links Replacer', 'affiliate-links' ),
			__( 'Link Replacer', 'affiliate-links' ),
			'manage_options',
			'replacer',
			array( $this, 'controller' )
		);
	}

	public function controller() {
		if ( current_user_can( 'manage_options' ) && isset( $_POST['replace_links_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['replace_links_nonce'] ) ), 'replace_links' ) ) {
			$this->current_link        = isset( $_POST['current-link'] ) ? esc_url_raw( wp_unslash( $_POST['current-link'] ) ) : '';
			$this->new_link            = isset( $_POST['new-link'] ) ? esc_url_raw( wp_unslash( $_POST['new-link'] ) ) : '';
			$status                    = $this->replace_link( $this->current_link, $this->new_link );
			/* translators: %s: number of links updated */
			$this->messages['message'] = sprintf( __( "Query executed OK, %s links updated", 'affiliate-links' ), $status );
		}
		$this->render_view( $this->template );
	}

	public function replace_link( $current_link, $new_link ) {
		global $wpdb;
		
		$sql = $wpdb->prepare(
			"UPDATE {$wpdb->posts} SET post_content = replace(post_content, %s, %s) WHERE {$wpdb->posts}.post_status='publish'",
			$current_link,
			$new_link
		);
		
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is already prepared above
		return $wpdb->query( $sql );
	}
}