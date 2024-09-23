<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/**
 * The Affiliate Links PRO Plugin Class.
 */
class Affiliate_Links_PRO {

    protected $stats_instance;

    /**
     * @var Affiliate_Links_Pro_Metabox
     */
    protected $custom_target_url_metabox;
    private static $instance = NULL;

    /**
     * Creates or returns an instance of this class.
     */
    public static function instance() {
        // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
        if ( NULL == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function __construct() {
        add_action( 'af_link_init', array( $this, 'load' ) );
        add_action( 'admin_init', array( $this, 'load_vendors' ) );

        $this->stats_instance        = Affiliate_Links_Pro_Stats::get_instance();
        new Affiliate_Links_Pro_Settings();
        new Affiliate_Links_Pro_Widgets();
        new Affiliate_Links_Pro_Shortcodes();
        new Affiliate_Links_Pro_Replacer();
        new Affiliate_Links_Pro_Import_Export();

        $this->custom_target_url_metabox = new Affiliate_Links_Pro_Metabox();

        add_action( 'admin_enqueue_scripts', array(
            $this,
            'enqueue_scripts',
        ) );
        add_action( 'wp_enqueue_scripts', array(
            $this,
            'load_resources'
        ) );
        add_action( 'wp_ajax_af_link_additional_settings', array(
            $this,
            'get_additional_settings',
        ) );
        add_filter( 'af_link_target_url', array(
            $this,
            'update_target_url'
        ) );
        add_filter( 'af_link_updated_target_url', array(
            $this,
            'keep_query_args'
        ) );
    }

     public function load_resources(){
           	wp_enqueue_script( 'affiliate-links-pro', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/front-af-links-pro.js', array( 'jquery' ), '1.6', TRUE );
    }

    public function enqueue_scripts( $hook ) {
        //css
        wp_enqueue_style( 'affiliate-links-pro-css', AFFILIATE_LINKS_PLUGIN_URL . 'pro/css/admin-af-links-pro.css', FALSE, '1.6' );

        // //js
        wp_enqueue_script( 'affiliate-links-pro', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/admin-af-links-pro.js', array( 'jquery' ), '1.6', TRUE );
        wp_localize_script( 'affiliate-links-pro', 'aLinkTargetUrl', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'action'   => 'af_link_additional_settings',
        ) );

        wp_enqueue_script( 'affiliate-links-pro-repeater', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jquery.repeater.js', array( 'jquery' ), '1.6', TRUE );

        $this->enqueue_report_scripts();
    }

    public function get_additional_settings() {
        if ( isset( $_REQUEST[ 'name' ] ) && FALSE === empty( $_REQUEST[ 'name' ] ) ) {
            foreach ( $this->custom_target_url_metabox->get_custom_target_url_values( $_REQUEST[ 'name' ] ) as $value => $label ) {
                ?>
                <option value="<?php echo esc_attr( $value ) ?>"><?php echo esc_html( $label ) ?></option>
                <?php
            }
            exit();
        }
    }

    public function enqueue_report_scripts() {
        $current_post_type = isset( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : '';
        $current_page      = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '';

        if ( 'affiliate-links' == $current_post_type && 'reports' == $current_page ) {
            wp_enqueue_script( 'affiliate-links-pro-jqplot', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jquery.jqplot.js', array( 'jquery' ), '1.6', TRUE );
            wp_enqueue_script( 'affiliate-links-pro-labelRender', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jqplot.canvasAxisLabelRenderer.js', array( 'jquery' ), '1.6', TRUE );
            wp_enqueue_script( 'affiliate-links-pro-labelTextRender', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jqplot.canvasTextRenderer.js', array( 'jquery' ), '1.6', TRUE );
            wp_enqueue_script( 'affiliate-links-pro-pie', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jqplot.pieRenderer.js', array( 'jquery' ), '1.6', TRUE );
            wp_enqueue_script( 'affiliate-links-pro-dateRender', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jqplot.dateAxisRenderer.js', array( 'jquery' ), '1.6', TRUE );
            wp_enqueue_script( 'affiliate-links-pro-donut', AFFILIATE_LINKS_PLUGIN_URL . 'pro/js/jqplot.donutRenderer.js', array( 'jquery' ), '1.6', TRUE );
            wp_enqueue_script( 'jquery-ui-datepicker' );

            wp_enqueue_style( 'affiliate-links-pro-jqplot', AFFILIATE_LINKS_PLUGIN_URL . 'pro/css/jquery.jqplot.css', FALSE, '1.6' );
            wp_enqueue_style( 'jquery-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
        }
    }

    public function load_vendors() {
        foreach ( glob( AFFILIATE_LINKS_PRO_PLUGIN_DIR . '/vendors/*.php' ) as $file ) {
            require_once $file;
        }
    }

    public function update_target_url( $target_url ) {
        $custom_target_url_rules            = $this->custom_target_url_metabox->get_browser_links();
        $current_settings                   = $this->get_user_current_settings();
        $this->stats_instance->current_link = $current_settings;

        if ( 0 !== count( $custom_target_url_rules ) ) {
            foreach ( $custom_target_url_rules as $custom_target_url_rule ) {
                $is_match = TRUE;
                foreach ( $custom_target_url_rule[ 'rules' ] as $rule ) {
                    if ( strcasecmp( $current_settings[ $rule[ 'name' ] ], $rule[ 'value' ] ) !== 0 && $rule[ 'cond' ] == 1 || strcasecmp( $current_settings[ $rule[ 'name' ] ], $rule[ 'value' ] ) == 0 && $rule[ 'cond' ] == 0
                    ) {
                        $is_match = FALSE;
                    }
                }
                if ( $is_match ) {
                    $target_url = esc_url_raw( $custom_target_url_rule[ 'url' ] );
                }
            }
        }

        return apply_filters( 'af_link_updated_target_url', $target_url );
    }

    public function keep_query_args( $target_url ) {
        $query_params = Affiliate_Links_Settings::get_option( 'parameters_whitelist' );

        if ( ! empty( $query_params ) ) {
            $query_params = explode( ',', $query_params );
            $query_args   = array();

            foreach ( $query_params as $key ) {
                $key = trim( $key );

                if ( isset( $_GET[ $key ] ) ) {
                    $query_args[ $key ] = (string) $_GET[ $key ];
                }
            }

            if ( ! empty( $query_args ) ) {
                $target_url = add_query_arg( $query_args, $target_url );
            }
        }

        return $target_url;
    }

    public function get_current_platform() {
        $platform = 'desktop';

        if ( wp_is_mobile() ) {
            $platform = 'mobile';
        }

        return $platform;
    }

    public function get_current_os() {
        $current_os = '';
        $user_agent = $_SERVER[ 'HTTP_USER_AGENT' ];

        foreach ( $this->custom_target_url_metabox->get_custom_target_url_values( 'os' ) as $name => $value ) {
            if ( preg_match( "/{$name}/i", $user_agent ) ) {
                $current_os = $value;
            }
        }

        return $current_os;
    }

    public function get_current_browser() {
        $current_browser = '';

        foreach ( $this->custom_target_url_metabox->get_custom_target_url_values( 'browser' ) as $name => $description ) {
            if ( $GLOBALS[ $name ] ) {
                $current_browser = $name;
            }
        }

        return $current_browser;
    }

    public function get_user_current_settings() {
        return array(
            'browser'  => $this->get_current_browser(),
            'os'       => $this->get_current_os(),
            'platform' => $this->get_current_platform(),
            'lang'     => substr( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ], 0, 2 ),
        );
    }

    public function get_browser_title( $browser_name ) {
        $available_browsers = $this->custom_target_url_metabox->get_custom_target_url_values( 'browser' );

        if ( isset( $available_browsers[ $browser_name ] ) ) {
            return $available_browsers[ $browser_name ];
        }

        return __( 'Unknown', 'affiliate-links' );
    }
}

function AFL_PRO() {
    return Affiliate_Links_PRO::instance();
}

AFL_PRO();
