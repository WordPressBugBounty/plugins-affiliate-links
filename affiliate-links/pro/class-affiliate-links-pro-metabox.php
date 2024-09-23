<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
include_once AFFILIATE_LINKS_PLUGIN_DIR . 'admin/class-affiliate-links-metabox.php';

class Affiliate_Links_Pro_Metabox extends Affiliate_Links_Metabox {


	/**
	 * Adds the meta box container.
	 */

	protected $default_browser = 'is_chrome';
	protected $template = '/views/html-additional-settings.php';
	protected $browser_link_meta_key = '_affiliate_links_additional_target_url';

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {

		// Add metabox actions.
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

	}


	public function add_meta_box( $post_type ) {

		$post_types = array( Affiliate_Links::$post_type );

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				'affiliate_links_additional',
				__( 'Custom Target URL', 'affiliate-links' ),
				array( $this, 'render_rules_content' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}


	public function render_rules_content() {
		global $post;
		$template_path = dirname( __FILE__ ) . $this->template;
		if ( ! file_exists( $template_path ) ) {
			return '';
		}
		require_once( $template_path );
	}

	public function get_custom_target_url_values( $key ) {
		$values = array();
		switch ( TRUE ) {
			case 'browser' == $key:
				$values = array(
					"is_chrome" => "Google Chrome",
					"is_safari" => "Safari",
					"is_opera"  => "Opera",
					"is_macIE"  => "Internet Explorer (Mac)",
					"is_winIE"  => "Internet Explorer (Windows)",
					"is_gecko"  => "Firefox",
					"is_IE"     => "Internet Explorer",
					'is_edge'   => "Microsoft Edge",
				);
				break;
			case 'platform' == $key:
				$values = array(
					'mobile'  => 'Mobile',
					'desktop' => 'Desktop',
				);
				break;
			case 'os' == $key:
				$values = array(
					'windows nt 10'      => 'Windows 10',
					'windows nt 6.3'     => 'Windows 8.1',
					'windows nt 6.2'     => 'Windows 8',
					'windows nt 6.1'     => 'Windows 7',
					'macintosh|mac os x' => 'Mac OS X',
					'mac_powerpc'        => 'Mac OS 9',
					'linux'              => 'Linux',
					'ubuntu'             => 'Ubuntu',
					'iphone'             => 'iPhone',
					'ipod'               => 'iPod',
					'ipad'               => 'iPad',
					'android'            => 'Android',
					'blackberry'         => 'BlackBerry',
					'webos'              => 'Mobile',
				);
				break;
			case 'lang' == $key:
				$values = array(
					'en' => 'English',
					'fr' => 'French',
					'de' => 'German',
					'it' => 'Italian',
					'pt' => 'Portuguese',
					'es' => 'Spanish',
					'ru' => 'Russian',
				);
				break;
		}

		return $values;
	}

	public function get_custom_target_url_keys() {
		return array(
			'browser'  => __( "Browser", 'affiliate-links' ),
			'platform' => __( "Platform", 'affiliate-links' ),
			'os'       => __( "OS", 'affiliate-links' ),
			'lang'     => __( "Language", 'affiliate-links' ),
		);
	}

	public function get_custom_target_url_condition() {
		return array(
			TRUE  => 'is equal to',
			FALSE => 'is not equal to',
		);
	}


	public function get_browser_links( $id = '' ) {
		global $post;
		$post_id = $id ? $id : $post->ID;
		$data    = get_post_meta( $post_id, $this->browser_link_meta_key );
		if ( count( $data ) ) {
			$data = maybe_unserialize( current( $data ) );
		}

		return $data;
	}


	public function get_default_browser() {
		return $this->default_browser;
	}

	public function get_browser_links_meta_key() {
		return $this->browser_link_meta_key;
	}

	public function save( $post_id ) {

		if ( $this->is_form_skip_save( $post_id ) ) {
			return $post_id;
		}

		$data = $this->validate_links( $_POST[ $this->browser_link_meta_key ] );
		$data = maybe_serialize( $data );
		update_post_meta( $post_id, $this->browser_link_meta_key, $data );
	}

	public function validate_links( $links_data ) {
		if ( ! count( $links_data ) ) {
			return array();
		}

		foreach ( $links_data as $key => $data ) {
			if ( isset( $data['template'] ) && $data['template'] == 1 && $data['url'] == '' ) {
				unset( $links_data[ $key ] );
				continue;
			}
			$links_data[ $key ]['url'] = esc_url_raw( $data['url'] );
		}

		return $links_data;
	}
}

