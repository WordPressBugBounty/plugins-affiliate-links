<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Affiliate Links Editor Buttons
 */
class Affiliate_Links_Buttons {

	public function __construct() {
		add_action( 'init', array( $this, 'add_buttons' ) );
	}

	public function add_buttons() {
		add_filter( 'mce_external_plugins', array( $this, 'add_button' ) );
		add_filter( 'mce_buttons', array( $this, 'register_button' ) );
	}


	public function add_button( $plugin_array ) {
		$plugin_array['affiliate_links'] = AFFILIATE_LINKS_PLUGIN_URL . 'admin/js/affiliate-links-button.js';

		return $plugin_array;
	}

	public function register_button( $buttons ) {
		array_push( $buttons, 'affiliate_links' );

		return $buttons;
	}

}

/**
 * Calls the class on the post edit screen.
 */
if ( is_admin() ) {

	new Affiliate_Links_Buttons();

}

// Add Quicktags
function affiliate_links_quicktags() {

	if ( wp_script_is( 'quicktags' ) ) {
		?>
        <script type="text/javascript">
            QTags.addButton('affiliate-links-button', 'Affiliate Link', affiliateLinksQTagsButton, '', '', 'Add Affiliate Link');

            function affiliateLinksQTagsButton(e, c, ed) {
                var URL, t = this;
                if (typeof afLink !== 'undefined') {
                    afLink.open(ed.id);
                }
            }

        </script>
		<?php
	}

}

add_action( 'admin_print_footer_scripts', 'affiliate_links_quicktags' );
