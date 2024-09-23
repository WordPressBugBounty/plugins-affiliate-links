<?php
/**
 * Plugin Name: Affiliate Links Lite
 * Description: A powerful tool for managing and masking affiliate links. Create any redirect links to any website from your WordPress Admin. Perfect for SEO, advertising and affiliate marketing activities.
 * Version:     3.0
 * Author:      Custom4Web
 * Author URI:  https://www.custom4web.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: affiliate-links
 * Domain Path: /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'AFFILIATE_LINKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AFFILIATE_LINKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AFFILIATE_LINKS_FILE', __FILE__ );
define( 'AFFILIATE_LINKS_BASENAME', plugin_basename( AFFILIATE_LINKS_FILE ) );
define( 'AFFILIATE_LINKS_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) . 'pro' );

require_once AFFILIATE_LINKS_PLUGIN_DIR . 'includes/class-affiliate-links.php';

/**
 * Begins execution of the plugin.
 */
$Affiliate_Links = new Affiliate_Links();

/*
* Activation/deactivation stuff
*/
register_activation_hook( __FILE__, array ( $Affiliate_Links, 'activation_hook') );
register_deactivation_hook( __FILE__, array ( $Affiliate_Links, 'deactivation_hook') );
