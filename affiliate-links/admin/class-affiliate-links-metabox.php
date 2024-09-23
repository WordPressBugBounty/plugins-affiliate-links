<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Custom Affiliate Links Metabox For Links Post Type.
 */
class Affiliate_Links_Metabox {

	/**
	 * List of custom fields.
	 */
	public $fields = array(
		array(
			'name'              => '_affiliate_links_target',
			'title'             => 'Link Target URL',
			'description'       => '* Enter your target URL',
			'type'              => 'url',
			'required'          => 'required',
			'sanitize_callback' => 'esc_url_raw',
		),
		array(
			'name'              => '_affiliate_links_target_two',
			'title'             => 'Second Target URL',
			'description'       => 'Enter second Target URL',
			'type'              => 'url',
			'sanitize_callback' => 'esc_url_raw',
		),
		array(
			'name'        => '_affiliate_links_description',
			'title'       => 'Link Description',
			'description' => 'Describe your link',
			'type'        => 'text',
		),
		array(
			'name'        => '_affiliate_links_iframe',
			'global_name' => 'iframe',
			'title'       => 'Mask Link',
			'type'        => 'checkbox',
			'description' => 'Open link in iframe',
		),
		array(
			'name'        => '_affiliate_links_nofollow',
			'global_name' => 'nofollow',
			'title'       => 'Nofollow Link',
			'type'        => 'checkbox',
			'description' => 'Add "X-Robots-Tag: noindex, nofollow" to HTTP headers',
		),
		array(
			'name'        => '_affiliate_links_redirect',
			'global_name' => 'redirect',
			'title'       => 'Redirect Type',
			'type'        => 'radio',
			'description' => 'Set redirection HTTP status code',
			'values'      => array(
				'301' => '301 Moved Permanently',
				'302' => '302 Found',
				'307' => '307 Temporary Redirect',
			),
		),
	);

	public $admin_grid_fields = array(
		'_affiliate_links_target',
		'_affiliate_links_description',
		'_affiliate_links_redirect',
	);

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {

		// Add metabox actions.
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

		// Add custom field values to admin grid columns.
		add_filter( 'manage_posts_columns', array( $this, 'columns_head' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_links_by_cat' ) );

		// Add custom styling.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Remove the Yoast SEO columns
		add_action( 'manage_edit-' . Affiliate_Links::$post_type . '_columns', array( $this, 'hide_yoast_columns' ) );

		// remove unnecessary screen options
		add_action( 'current_screen', array( $this, 'get_screen_options' ) );

		// remove view mode screen options
		add_filter(
			'view_mode_post_types',
			array(
				$this,
				'remove_view_mode',
			)
		);
	}

	/**
	 * Remove unused Yoast columns
	 */
	public function hide_yoast_columns( $columns ) {

		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );

		return $columns;
	}

	/**
	 * Add admin css file.
	 */
	public function enqueue_scripts( $hook ) {
		global $post;
		if ( $hook != 'post.php' and $hook != 'post-new.php' and $hook != 'affiliate-links_page_affiliate_links' ) {
			return;
		}

		// css
		wp_register_style( 'affiliate-links-css', AFFILIATE_LINKS_PLUGIN_URL . 'admin/css/affiliate-links-admin.css', false, '1.6' );
		wp_enqueue_style( 'affiliate-links-css' );

		// js
		wp_register_script( 'affiliate-links-js', AFFILIATE_LINKS_PLUGIN_URL . 'admin/js/affiliate-links-admin.js', array( 'jquery' ), '1.6', true );

		if ( $post ) {
			wp_localize_script(
				'affiliate-links-js',
				'afLinksAdmin',
				array(
					'linkId'    => $post->ID,
					'permalink' => get_the_permalink( $post->ID ),
					'shortcode' => 'af_link',
				)
			);
		}

		wp_enqueue_script( 'affiliate-links-js', false, array( 'jquery' ), '1.6', true );
	}

	public function get_screen_options( $screen ) {
		if ( 'edit-affiliate-links' !== $screen->id ) {
			add_filter(
				"manage_{$screen->id}_columns",
				array(
					$this,
					'manage_screen_options',
				)
			);
		}
	}

	public function manage_screen_options( $columns ) {
		if ( isset( $columns['hits'] ) ) {
			return array();
		}

		return $columns;
	}

	/**
	 * Modify admin grid column headers.
	 */
	public function columns_head( $defaults ) {

		global $typenow;

		if ( $typenow == Affiliate_Links::$post_type ) {

			$defaults['permalink'] = __( 'Link URL', 'affiliate-links' );

			foreach ( $this->get_fields() as $field ) {

				if ( in_array( $field['name'], $this->admin_grid_fields ) ) {
					$defaults[ $field['name'] ] = $field['title'];
				}
			}

			$defaults['_affiliate_links_stat'] = __( 'Hits', 'affiliate-links' );

		}

		return $defaults;
	}

	/**
	 * Modify admin grid columns.
	 */
	public function columns_content( $column_name, $post_id ) {

		switch ( $column_name ) {
			case 'permalink':
				echo esc_html( get_the_permalink( $post_id ) );
				break;
			case '_affiliate_links_target':
				echo esc_html( get_post_meta( $post_id, '_affiliate_links_target', true ) );
				break;
			case '_affiliate_links_stat':
				echo esc_html( $this->get_link_hits( $post_id ) );
				break;
			case '_affiliate_links_description':
				echo esc_html( get_post_meta( $post_id, '_affiliate_links_description', true ) );
				break;
			case '_affiliate_links_redirect':
				echo esc_html( get_post_meta( $post_id, '_affiliate_links_redirect', true ) );
				break;
			case '_affiliate_links_nofollow':
				echo esc_html( get_post_meta( $post_id, '_affiliate_links_nofollow', true ) );
				break;
		}
	}

	/**
	 * Add link category filter to admin grid.
	 */
	function restrict_links_by_cat() {

		global $typenow;
		global $wp_query;

		if ( $typenow == Affiliate_Links::$post_type ) {

			if ( ! empty( $wp_query->query[ Affiliate_Links::$taxonomy ] ) ) {
				$selected = $wp_query->query[ Affiliate_Links::$taxonomy ];
			} else {
				$selected = 0;
			}

			wp_dropdown_categories(
				array(
					'show_option_all' => __( 'All Categories', 'affiliate-links' ),
					'taxonomy'        => Affiliate_Links::$taxonomy,
					'value_field'     => 'slug',
					'name'            => Affiliate_Links::$taxonomy,
					'orderby'         => 'name',
					'selected'        => $selected,
					'hierarchical'    => true,
					'depth'           => 3,
					'show_count'      => true,
					'hide_empty'      => true,
					'hide_if_empty'   => 1,
				)
			);

		}
	}

	/**
	 * Add appropriate actions.
	 */
	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 0 );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {

		$post_types = array( Affiliate_Links::$post_type );

		if ( in_array( $post_type, $post_types ) ) {

			add_meta_box(
				'affiliate_links_settings',
				__( 'Link Settings', 'affiliate-links' ),
				array( $this, 'render_metabox_content' ),
				$post_type,
				'normal',
				'high'
			);

			add_meta_box(
				'affiliate_links_embed',
				__( 'Link Embedding', 'affiliate-links' ),
				array( $this, 'render_metabox_embed' ),
				$post_type,
				'normal',
				'high'
			);

		}
	}

	public function is_form_skip_save( $post_id ) {
		return ( ! isset( $_POST['affiliate_links_custom_box_nonce'] ) )
				|| ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['affiliate_links_custom_box_nonce'] ) ), 'affiliate_links_custom_box' ) )
				|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				|| ( ! current_user_can( 'edit_post', $post_id ) );
	}

	/**
	 * Save metabox.
	 */
	public function save( $post_id ) {

		if ( $this->is_form_skip_save( $post_id ) ) {
			return $post_id;
		}

		foreach ( $this->get_fields() as $field ) {

			// Update the meta field.
			update_post_meta( $post_id, $field['name'], $this->get_sanitized_value( $field ) );

		}

		foreach ( $this->get_embedded_metabox_fields() as $field ) {

			// Update the meta field.
			update_post_meta( $post_id, $field['name'], $this->get_sanitized_value( $field ) );

		}

		// reset stat count
		if ( isset( $_POST['_affiliate_links_stat'] ) ) {
			$count = (int) sanitize_key( $_POST['_affiliate_links_stat'] );
			// Update the meta field.
			update_post_meta( $post_id, '_affiliate_links_stat', $count );
		}
	}

	public function get_sanitized_value( $field ) {
		if ( ! isset( $_POST[ $field['name'] ] ) ) {
			return '';
		}
		$sanitize_callback = ( isset( $field['sanitize_callback'] ) ) ? $field['sanitize_callback'] : 'sanitize_text_field';

		return call_user_func( $sanitize_callback, $_POST[ $field['name'] ] );
	}

	public function get_fields() {
		return apply_filters( 'af_links_get_fields', $this->fields );
	}

	/**
	 * Render metabox content.
	 */
	public function render_metabox_content( $post ) {
		global $post_type_object;
		echo '<table class="form-table">';

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'affiliate_links_custom_box', 'affiliate_links_custom_box_nonce' );

		$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html( $post->ID ) : '';

		if ( $post_type_object->public
			&& ! ( 'pending' == get_post_status( $post ) && ! current_user_can( $post_type_object->cap->publish_posts ) )
		) {
			$has_sample_permalink = $sample_permalink_html && 'auto-draft' != $post->post_status;
			if ( $has_sample_permalink ) {
				$this->link_field( $post->ID );
			}
		}

		$this->render_fields( $post->ID );

		$this->stats_field( $post->ID );

		echo '</table>';
	}

	public function render_fields( $id ) {
		foreach ( $this->get_fields() as $field ) {

			// Retrieve an existing value from the database.
			$value = ( isset( $field['name'] ) ) ? get_post_meta( $id, $field['name'], true ) : '';
			$this->render_field( $field, $value );

		}
	}

	public function get_embedded_metabox_fields() {
		return array(
			array(
				'name'        => '_embedded_add_rel',
				'thead'       => __( 'Add rel="nofollow"', 'affiliate-links' ),
				'class'       => 'affiliate_links_control',
				'data-attr'   => 'rel',
				'data-value'  => 'nofollow',
				'description' => __( 'Discourage search engines from following this link', 'affiliate-links' ),
				'type'        => 'embed_checkbox',
			),
			array(
				'name'        => '_embedded_add_target',
				'thead'       => __( 'Add target="_blank', 'affiliate-links' ),
				'class'       => 'affiliate_links_control',
				'data-attr'   => 'target',
				'data-value'  => '_blank',
				'description' => __( 'Link will be opened in a new browser tab', 'affiliate-links' ),
				'type'        => 'embed_checkbox',
			),
			array(
				'name'        => '_embedded_add_link_title',
				'thead'       => __( 'Add link title', 'affiliate-links' ),
				'class'       => 'affiliate_links_control',
				'data-attr'   => 'title',
				'description' => __( 'Title text on link hover', 'affiliate-links' ),
				'type'        => 'embed_text',
			),
			array(
				'name'        => '_embedded_add_link_class',
				'thead'       => __( 'Add link class', 'affiliate-links' ),
				'class'       => 'affiliate_links_control',
				'data-attr'   => 'class',
				'description' => __( 'CSS class for custom styling', 'affiliate-links' ),
				'type'        => 'embed_text',
			),
			array(
				'name'        => '_embedded_add_link_anchor',
				'thead'       => __( 'Add link anchor', 'affiliate-links' ),
				'class'       => 'affiliate_links_control',
				'data-attr'   => 'anchor',
				'description' => __( 'Clickable link text', 'affiliate-links' ),
				'type'        => 'embed_text',
			),

		);
	}

	/**
	 * Render embed metabox content.
	 */
	public function render_metabox_embed( $post ) {
		global $post_type_object;

		$sample_permalink_html = $post_type_object->public ? get_sample_permalink_html( $post->ID ) : '';

		if ( $post_type_object->public
			&& ! ( 'pending' == get_post_status( $post ) && ! current_user_can( $post_type_object->cap->publish_posts ) )
		) {
			$has_sample_permalink = $sample_permalink_html && 'auto-draft' != $post->post_status;
			if ( $has_sample_permalink ) {
				add_filter( 'af_links_get_fields', array( $this, 'get_embedded_metabox_fields' ) );

				echo '<table class="form-table hide-if-no-js">';
				$this->render_fields( $post->ID );
				echo '</table>';
				load_template( __DIR__ . '/partials/metabox-embed.php' );
			} else {
				echo '<p>' . esc_html__( 'Before you can use this link you need to publish it.' ) . '</p>';
			}
		}
	}

	/**
	 * Generate settings field html.
	 */
	public function render_field( $field, $value ) {

		$func_name = 'render_' . $field['type'] . '_field';

		if ( method_exists( __CLASS__, $func_name ) ) {

			call_user_func_array(
				array( $this, $func_name ),
				array(
					'field' => $field,
					'value' => $value,
				)
			);

		} else {

			call_user_func_array(
				array( $this, 'render_text_field' ),
				array(
					'field' => $field,
					'value' => $value,
				)
			);

		}
	}

	/**
	 * Generate text input field.
	 */
	public function render_text_field( $field, $value ) {

		$name  = esc_attr( $field['name'] );
		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );
		?>
		<tr>
			<th>
				<label for="<?php echo $name; ?>" class="<?php echo $name; ?>_label"><?php echo $title; ?></label>
			</th>
			<td>
				<input
					type="<?php echo $type; ?>"
					id="<?php echo $name; ?>"
					name="<?php echo $name; ?>"
					class="<?php echo $name; ?>_field"
					<?php
					if ( ! empty( $field['required'] ) ) {
						echo $field['required']; }
					?>
					value="<?php echo esc_attr( $value ); ?>"
				>
				<p class="description"><?php echo $desc; ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generate checkbox field.
	 */
	public function render_checkbox_field( $field, $value ) {

		$name  = esc_attr( $field['name'] );
		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );

		if ( ! empty( Affiliate_Links::$settings[ $field['global_name'] ] ) ) {
			$default_val = Affiliate_Links::$settings[ $field['global_name'] ];
		} else {
			$default_val = 0;
		}

		$checked_value = ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) ? $value : $default_val;
		?>
		<tr>
			<th>
				<label for="<?php echo $name; ?>" class="<?php echo $name; ?>_label"><?php echo $title; ?></label>
			</th>
			<td>
				<input
					type="<?php echo $type; ?>"
					id="<?php echo $name; ?>"
					name="<?php echo $name; ?>"
					class="<?php echo $name; ?>_field"
					value="1"
					<?php checked( $checked_value, 1 ); ?>
				>
				<label for="<?php echo $name; ?>">
					<?php echo $desc; ?>
				</label>
			</td>
		</tr>
		<?php
	}

	public function render_embed_checkbox_field( $field, $value ) {
		?>
		<tr>
			<th>
				<?php echo esc_html( $field['thead'] ); ?>
			</th>
			<td>
				<label>
					<input type="<?php echo esc_attr( trim( $field['type'], 'embed_' ) ); ?>"
							name="<?php echo esc_attr( $field['name'] ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							data-attr="<?php echo esc_attr( $field['data-attr'] ); ?>"
							data-value="<?php echo esc_attr( $field['data-value'] ); ?>"
							value="1"
							<?php checked( $value, 1 ); ?>
					>
					<?php echo esc_html( $field['description'] ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	public function render_embed_text_field( $field, $value ) {
		?>
		<tr>
			<th>
				<?php echo esc_html( $field['thead'] ); ?>
			</th>
			<td>
				<label>
					<input type="<?php echo esc_attr( trim( $field['type'], 'embed_' ) ); ?>"
							name="<?php echo esc_attr( $field['name'] ); ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							data-attr="<?php echo esc_attr( $field['data-attr'] ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
					>
					<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generate radio button fields.
	 */
	public function render_radio_field( $field, $value ) {

		$title = esc_attr( $field['title'] );
		$desc  = esc_html( $field['description'] );
		$type  = esc_attr( $field['type'] );

		$values = $field['values'];
		reset( $values );
		$default_val = key( $values );

		if ( ! empty( Affiliate_Links::$settings[ $field['global_name'] ] ) ) {
			$default_val = Affiliate_Links::$settings[ $field['global_name'] ];
		}

		$checked_value = empty( $value ) ? $default_val : $value;
		?>
		<tr>
			<th><?php echo $title; ?></th>
			<td>
				<?php foreach ( $values as $key => $value ) { ?>
					<input
						type="<?php echo $type; ?>"
						id="<?php echo esc_attr( $field['name'] . '_' . $key ); ?>"
						name="<?php echo esc_attr( $field['name'] ); ?>"
						value="<?php echo esc_attr( $key ); ?>"
						<?php checked( $checked_value, $key ); ?>
					>
					<label for="<?php echo esc_attr( $field['name'] . '_' . $key ); ?>">
						<?php echo esc_html( $value ); ?>
					</label>
					<br>
				<?php } ?>
				<p class="description"><?php echo $desc; ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Generate fields for hit stats displaying.
	 */
	public function stats_field( $post_id ) {

		$count = esc_html( $this->get_link_hits( $post_id ) );
		?>
		<tr>
			<th><?php esc_html_e( 'Total Hits', 'affiliate-links' ); ?></th>
			<td>
				<?php if ( $count ) { ?>
					<span class="affiliate_links_total_count"><?php echo $count; ?></span>

				<?php } else { ?>
					<span class="affiliate_links_total_count">-</span>
				<?php } ?>
				<p class="description"><?php esc_html_e( 'Total count of link redirects', 'affiliate-links' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public function get_link_hits( $post_id ) {

		global $wpdb;

		return $wpdb->get_var( "SELECT count(link_id) as hits FROM {$wpdb->prefix}af_links_activity WHERE link_id=$post_id" );
	}

	/**
	 * Generate fields for permalink displaying.
	 */
	public function link_field( $post_id ) {

		?>
		<tr>
			<th><?php esc_html_e( 'Your link', 'affiliate-links' ); ?></th>
			<td>
				<span class="affiliate_links_copy_link">
					<?php
					the_permalink( $post_id );
					echo get_post_meta( $post_id, '_affiliate_links_target_two', true ) ? '?afbclid=1' : '';
					?>
				</span>
				<span class="affiliate_links_copy_button">
					<button type="button"
							class="button button-small hide-if-no-js"><?php esc_html_e( 'Copy', 'affiliate-links' ); ?></button>
				</span>
				<p class="description"><?php esc_html_e( 'To change this link you should edit Permalink at the top of screen', 'affiliate-links' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public function remove_view_mode( $view_mode_post_types ) {

		unset( $view_mode_post_types['affiliate-links'] );

		return $view_mode_post_types;
	}
}

/**
 * Calls the class on the post edit screen.
 */
if ( is_admin() ) {

	new Affiliate_Links_Metabox();

}
