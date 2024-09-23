<?php
/*
* Plugin uninstall
*/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	exit;
}

// here we go

global $wpdb;

define( 'AFFILIATE_LINKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AFFILIATE_LINKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once AFFILIATE_LINKS_PLUGIN_DIR . 'includes/class-affiliate-links.php';

$post_type = Affiliate_Links::$post_type;

// Delete options
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'affiliate_links%';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'affiliate_license%';" );

// Delete posts + data
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( '%s' );", $post_type ) );
$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

// Delete terms + data
$taxonomy = Affiliate_Links::$taxonomy;

$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

if ( $terms ) {
    foreach ( $terms as $term ) {
        $wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
        $wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
    }
}

$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );

//delete stats table
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}af_links_activity" );
