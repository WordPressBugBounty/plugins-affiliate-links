<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die();
}
/**
 * Custom Affiliate Links Configuration Page.
 */
class Affiliate_Links_Settings {

    const DEFAULT_TAB = 'general';

	const SETTINGS_PAGE = 'affiliate_links';

    /**
     * List of settings fields.
     */
    public static $fields;

	public static $tabs;

    /**
     * Flag to track if translations have been applied.
     */
    private static $translations_applied = false;

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {

        // Initialize fields without translations
        self::$fields = self::get_default_fields();
		self::$tabs   = self::get_default_tabs();

        add_action( 'admin_menu',  array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_filter( 'plugin_action_links_' . AFFILIATE_LINKS_BASENAME, array( $this, 'add_action_links' ) );

        // Apply translations after plugins_loaded
        add_action( 'init', array( $this, 'apply_field_translations' ), 5 );

    }

    /**
	 * List of settings fields.
	 */
	public static function get_default_fields() {
		return array(
			array(
				'name'        => 'slug',
				'title'       => 'Affiliate Link Base',
				'title_i18n'  => true,
				'type'        => 'text',
				'tab'         => 'general',
				'default'     => 'go',
				'description' => 'You can change the default base part \'%1$s/go/%2$s\' of your redirect link to something else',
				'description_i18n' => true,
				'description_has_sprintf' => true,
			),
			array(
				'name'        => 'affiliate_api_key',
				'title'       => 'Wecantrack API Key',
				'title_i18n'  => true,
				'type'        => 'password',
				'tab'         => 'general',
				'default'     => '',
				'description' => 'Enter your wecantrack API key to utilise our affiliate link generator.',
				'description_i18n' => true,
			),
			array(
				'name'        => 'category',
				'title'       => 'Show Category in Link URL',
				'title_i18n'  => true,
				'type'        => 'checkbox',
				'tab'         => 'general',
				'description' => 'Show the link category slug in the affiliate link URL',
				'description_i18n' => true,
			),

			array(
				'name'        => 'default',
				'title'       => 'Default URL for Redirect',
				'title_i18n'  => true,
				'type'        => 'text',
				'tab'         => '',
				'default'     => get_home_url(),
				'description' => 'Enter the default URL for redirect if correct URL not set',
				'description_i18n' => true,
			),
			array(
				'name'        => 'nofollow',
				'title'       => 'Nofollow Affiliate Links',
				'title_i18n'  => true,
				'type'        => 'checkbox',
				'tab'         => 'defaults',
				'description' => 'Add "X-Robots-Tag: noindex, nofollow" to HTTP headers',
				'description_i18n' => true,
			),
			array(
				'name'        => 'redirect',
				'title'       => 'Redirect Type',
				'title_i18n'  => true,
				'type'        => 'radio',
				'tab'         => 'defaults',
				'default'     => '301',
				'description' => 'Set redirection HTTP status code',
				'description_i18n' => true,
				'values'      => array(
					'301' => array( 'label' => '301 Moved Permanently', 'i18n' => true ),
					'302' => array( 'label' => '302 Found', 'i18n' => true ),
					'307' => array( 'label' => '307 Temporary Redirect', 'i18n' => true ),
				),
			),

		);
	}

    public static function get_default_tabs() {
		return array(
			'general'  => 'General',
			'defaults' => 'Defaults',
		);
	}

    /**
     * Apply translations to fields after text domain is loaded.
     */
    public function apply_field_translations() {
        if ( self::$translations_applied ) {
            return;
        }

        foreach ( self::$fields as &$field ) {
            // Translate title if needed
            if ( ! empty( $field['title_i18n'] ) && $field['title_i18n'] === true ) {
                $field['title'] = __( $field['title'], 'affiliate-links' );
            }

            // Translate description if needed
            if ( ! empty( $field['description_i18n'] ) && $field['description_i18n'] === true ) {
                if ( ! empty( $field['description_has_sprintf'] ) ) {
                    $field['description'] = sprintf(
                        /* translators: 1: Open tag strong 2: Close tag strong */
                        __( $field['description'], 'affiliate-links' ),
                        '<strong>',
                        '</strong>'
                    );
                } else {
                    $field['description'] = __( $field['description'], 'affiliate-links' );
                }
            }

            // Translate radio/select values if needed
            if ( ! empty( $field['values'] ) && is_array( $field['values'] ) ) {
                foreach ( $field['values'] as $key => &$value ) {
                    if ( is_array( $value ) && ! empty( $value['i18n'] ) && $value['i18n'] === true ) {
                        $field['values'][$key] = __( $value['label'], 'affiliate-links' );
                    }
                }
            }
        }

        self::$translations_applied = true;
    }

    public static function get_field( $field_name ) {
		foreach ( self::$fields as $field ) {
			if ( $field['name'] == $field_name ) {
				return $field;
			}
		}

		return '';
	}

    public static function get_field_attr( $field_name, $attr ) {
		$field = self::get_field( $field_name );

		if ( $field && isset( $field[ $attr ] ) ) {
			return $field[ $attr ];
		}

		return '';
	}

    public static function add_field( $field ) {
		// If translations have already been applied and the field contains translatable text,
		// apply translations immediately
		if ( self::$translations_applied ) {
			if ( ! empty( $field['title_i18n'] ) && $field['title_i18n'] === true ) {
				$field['title'] = __( $field['title'], 'affiliate-links' );
			}
			if ( ! empty( $field['description_i18n'] ) && $field['description_i18n'] === true ) {
				$field['description'] = __( $field['description'], 'affiliate-links' );
			}
		}
		array_push( self::$fields, $field );
	}

	public function remove_filed( $field_name ) {
		foreach ( self::$fields as $key => $field ) {
			if ( $field['name'] == $field_name ) {
				unset( self::$fields[ $key ] );
			}
		}
	}

	public static function add_tab( $tab_name, $tab_title ) {
		self::$tabs[ $tab_name ] = $tab_title;
	}

	public static function remove_tab( $tab_name ) {
		if ( isset( self::$tabs[ $tab_name ] ) ) {
			unset( self::$tabs[ $tab_name ] );
		}
	}

    public function add_admin_menu() {

        add_submenu_page(
            'edit.php?post_type=affiliate-links',
            'Affiliate Links Settings',
            'Settings',
            'manage_options',
            self::SETTINGS_PAGE,
            array( $this, 'affiliate_links_options_page' )
        );

    }

	/**
	 * Add settings links
	 */
	function add_action_links( $links ) {

		$links[] = '<a href="' . admin_url( 'edit.php?post_type=affiliate-links&page=affiliate_links' ) . '">' . esc_html__( 'Settings', 'affiliate-links' ) . '</a>';

		return $links;

	}

    /**
     * Register plugin settings.
     */
    public function settings_init() {
        $current_tab = $this->get_current_tab();

        register_setting( self::SETTINGS_PAGE, 'affiliate_links_settings', array(
			$this,
			'affiliate_links_save_value',
		) );

		add_settings_section( 'affiliate_links_' . $current_tab, '', array(
			$this,
			'render_affiliate_links_' . $current_tab,
		), self::SETTINGS_PAGE );

		foreach ( self::$fields as $field ) {
			if ( isset( $field['tab'] ) && $current_tab == $field['tab'] ) {
				add_settings_field(
					$field['name'],
					$field['title'], // Already translated in apply_field_translations()
					array( $this, 'render_' . $field['type'] . '_field' ),
					self::SETTINGS_PAGE,
					self::SETTINGS_PAGE . '_' . $field['tab'],
					$field
				);
			}
		}
    }

    public function affiliate_links_save_value( $input ) {
		$af_setting  = get_option( 'affiliate_links_settings' );
		$field_value = ! empty( $af_setting ) ? $af_setting : array();

		$defaults = array(
			'category'  => array( 'tab' => 'general', 'value' => 0 ),
			'nofollow'  => array( 'tab' => 'defaults', 'value' => 0 ),
		);

		foreach ( $defaults as $name => $data ) {
			if ( $data['tab'] == $this->get_current_tab() && ! isset( $input['category'] ) ) {
				unset( $field_value[ $name ] );
			}
			if ( $data['tab'] == $this->get_current_tab() && ! isset( $input['nofollow'] ) ) {
				unset( $field_value[ $name ] );
			}
		}

		$input = array_replace( $field_value, $input );

		return $input;
	}

    public function render_affiliate_links_general() {
		submit_button();
	}

	public function render_affiliate_links_defaults() {
		submit_button();
	}

    /**
     * Generate text input field.
     */
    public function render_text_field($args) {
        $value = self::get_option( $args['name'] );
        ?>
        <input
            type="text"
            name="affiliate_links_settings[<?php echo esc_attr( $args['name'] ) ?>]"
            value="<?php echo esc_attr( $value ) ?>"
            placeholder="<?php echo ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '' ?>"
        >
        <p class="description">
            <?php echo wp_kses( $args['description'], array( 'strong' => array() ) ); ?>
        </p>
        <?php

    }

    /**
     * Generate checkbox field.
     */
    public function render_checkbox_field($args) {
		$checked_value = (int) self::get_option( $args['name'] );
        ?>
        <input
            type="checkbox"
            id="<?php echo esc_attr( $args['name'] ) ?>"
            name="affiliate_links_settings[<?php echo esc_attr( $args['name'] ) ?>]"
            value="1"
            <?php checked( $checked_value, 1 ) ?>
        >
        <?php echo esc_html( $args['description'] ); ?>
        <?php
    }

    /**
     * Generate radio button fields.
     */
    public function render_radio_field($args) {
        $values = $args['values'];
		reset( $values );
		$checked_value = self::get_option( $args['name'] );

        foreach( $values as $key => $value ) {
        ?>
            <input
                type="radio"
                id="<?php echo esc_attr( $args['name'] . '_' . $key ) ?>"
                name="affiliate_links_settings[<?php echo esc_attr( $args['name'] ) ?>]"
                value="<?php echo esc_attr( $key ) ?>"
                <?php checked( $checked_value, $key ) ?>
            >
            <label for="<?php echo esc_attr( $args['name'] . '_' . $key ) ?>">
                <?php echo esc_html(  $value ) ?>
            </label>
            <br>
        <?php
        }
        ?>
        <p class="description">
            <?php echo esc_html( $args['description'] ); ?>
        </p>
        <?php

    }

    /**
     * Plugin settings page HTML.
     */
    public function affiliate_links_options_page() {

        $this->flush_rules();

		$current_tab = $this->get_current_tab();
        ?>
        <div id="af_links-wrapper">
            <div class="wrap" id="af_links-primary">
                <h1><?php esc_html_e( 'Affiliate Links Settings', 'affiliate-links' ) ?></h1>
                <form action="options.php" method="post" id="af_links-settings-form">

                    <h2 class="nav-tab-wrapper" id="af_links-nav-tabs">
						<?php foreach ( self::$tabs as $name => $label ): ?>
                            <a href="<?php echo esc_url( $this->get_tab_url( $name ) ); ?>"
                               class="nav-tab <?php echo( $current_tab == $name ? 'nav-tab-active' : '' ) ?>">
								<?php echo esc_html( $label ); ?>
                            </a>
						<?php endforeach; ?>
                    </h2>

					<?php settings_fields( self::SETTINGS_PAGE ); ?>

                    <div class="af_links-nav-tab active" id="af_links_general">
						<?php $this->do_settings_sections( self::SETTINGS_PAGE ); ?>
                    </div>
                    <input type="hidden" name="tab" value="<?php echo esc_attr( $this->get_current_tab() ); ?>">
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Flush permalinks rules.
     */
    public function flush_rules() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Just checking if settings were updated
		if ( current_user_can( 'manage_options' ) && isset( $_GET['settings-updated'] ) ) {
			flush_rewrite_rules();
		}

    }

    /**
	 * Retrieve current tab
	 */
	public function get_current_tab() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation doesn't need nonce
		if ( ! empty( $_REQUEST['tab'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Tab value is validated against whitelist below
			$_tab = sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) );

			return $_tab;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation doesn't need nonce
		if ( isset( $_GET['tab'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Tab value is validated against whitelist below
			$_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			if ( isset( self::$tabs[ $_tab ] ) ) {
				return $_tab;
			}
		}

		return self::DEFAULT_TAB;
	}

    function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

			echo '<table id="af-link-form-table" class="form-table">';
			$this->do_settings_fields( $page, $section['id'] );
			echo '</table>';
			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}
		}
	}

    public function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$class = '';

			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
			}

			printf( '<tr%s>', $class );

			if ( ! empty( $field['args']['label_for'] ) ) {
				echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . esc_html( $field['title'] ) . '</label></th>';
			} else {
				echo '<th scope="row">' . esc_html( $field['title'] ) . '</th>';
			}

			echo '<td>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
			echo '</tr>';
		}
	}

	public function render_password_field($args) {
		$value = self::get_option($args['name']);
		?>
		<div style="position: relative; display: inline-block;">
			<input
				type="password"
				id="<?php echo esc_attr($args['name']); ?>"
				name="affiliate_links_settings[<?php echo esc_attr($args['name']); ?>]"
				value="<?php echo esc_attr($value); ?>"
				placeholder="<?php echo !empty($args['placeholder']) ? esc_attr($args['placeholder']) : ''; ?>"
				style="padding-right: 30px;"
			>
			<span
				onclick="togglePassword('<?php echo esc_attr($args['name']); ?>')"
				style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
			>
				<i id="eye_icon_<?php echo esc_attr($args['name']); ?>" class="dashicons dashicons-visibility"></i>
			</span>
		</div>
	
		<p class="description">
			<?php echo wp_kses($args['description'], array('strong' => array())); ?>
		</p>
	
		<script type="text/javascript">
			function togglePassword(fieldId) {
				var passwordField = document.getElementById(fieldId);
				var eyeIcon = document.getElementById("eye_icon_" + fieldId);
				if (passwordField.type === "password") {
					passwordField.type = "text";
					eyeIcon.classList.remove("dashicons-visibility");
					eyeIcon.classList.add("dashicons-hidden");
				} else {
					passwordField.type = "password";
					eyeIcon.classList.remove("dashicons-hidden");
					eyeIcon.classList.add("dashicons-visibility");
				}
			}
		</script>
	
		<style>
			.dashicons-visibility:before { content: "\f177"; }
			.dashicons-hidden:before { content: "\f530"; }
		</style>
		<?php
	}

    public function get_tab_url( $tab ) {
		return add_query_arg( array(
			'post_type' => Affiliate_Links::$post_type,
			'page'      => self::SETTINGS_PAGE,
			'tab'       => $tab,
		), admin_url( 'edit.php' )
		);
	}

    public static function get_option( $option ) {
		if ( isset( Affiliate_Links::$settings[ $option ] ) ) {
			return Affiliate_Links::$settings[ $option ];
		}

		return self::get_field_attr( $option, 'default' );
	}
}