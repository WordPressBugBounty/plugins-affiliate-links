<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * @var $this Affiliate_Links_Pro_Metabox
 */
$target_links_data = $this->get_browser_links();
$main_target_url   = current( get_post_meta( $post->ID, '_affiliate_links_target' ) );
?>

<div class="repeater">
    <div data-repeater-list="<?php echo esc_attr( $this->get_browser_links_meta_key() ); ?>"
         class="sortable">
		<?php if ( ! count( $target_links_data ) ): ?>
            <div data-repeater-item class="select2-drop" style="display: none">
                <input type="hidden" name="template" value="1">
                <label class="select2-offscreen"><?php esc_html_e( 'Target URL', 'affiliate-links' ); ?>
                    :</label>
                <input type="text" name="url" value=""
                       style="width: 100%; margin-bottom: 6px">
                <input data-repeater-delete type="button"
                       class="button button-secondary"
                       value="<?php esc_html_e( 'Delete', 'affiliate-links' ) ?>">

                <div class="inner-repeater">
                    <label class="select2-offscreen"><?php esc_html_e( 'Rule', 'affiliate-links' ) ?>
                        :</label>
                    <div data-repeater-list="rules">
                        <div data-repeater-item>
                            <select name="name" class="rule-name" required>
								<?php foreach ( $this->get_custom_target_url_keys() as $value => $label ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
                            </select>

                            <select name="cond" class="rule-cond" required>
								<?php foreach ( $this->get_custom_target_url_condition() as $value => $label ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
                            </select>

                            <select name="value" class="rule-value"
                                    style="width: 30%" required>
								<?php foreach ( $this->get_custom_target_url_values( 'browser' ) as $value => $label ): ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
                            </select>

                            <input data-repeater-delete type="button"
                                   class="button button-secondary"
                                   value="<?php esc_html_e( 'Delete', 'affiliate-links' ) ?>">
                        </div>
                    </div>
                    <input data-repeater-create type="button"
                           class="button button-secondary"
                           style="margin-top: 6px"
                           value="<?php esc_html_e( 'And', 'affiliate-links' ) ?>"/>
                </div>

                <p style="font-weight: bold"><?php esc_html_e( 'or', 'affiliate-links' ) ?></p>

            </div>
		<?php else: ?>
			<?php foreach ( $target_links_data as $link_data ): ?>
                <div data-repeater-item class="select2-drop">

                    <label class="select2-offscreen"><?php esc_html_e( 'Target URL', 'affiliate-links' ) ?>
                        :</label>
                    <input type="text" name="url"
                           value="<?php echo esc_url( $link_data['url'] ) ?>"
                           style="width: 100%; margin-bottom: 6px" required>
                    <input data-repeater-delete type="button"
                           class="button button-secondary"
                           value="<?php esc_html_e( 'Delete', 'affiliate-links' ) ?>">

                    <div class="inner-repeater">
                        <label class="select2-offscreen"><?php esc_html_e( 'Rule', 'affiliate-links' ) ?>
                            :</label>
                        <div data-repeater-list="rules">
							<?php foreach ( $link_data['rules'] as $rule ): ?>
                                <div data-repeater-item>
                                    <select name="name" class="rule-name"
                                            required>
										<?php foreach ( $this->get_custom_target_url_keys() as $value => $label ): ?>
                                            <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $rule['name'] ) ?>>
												<?php echo esc_html( $label ); ?>
                                            </option>
										<?php endforeach; ?>
                                    </select>

                                    <select name="cond" class="rule-cond"
                                            required>
										<?php foreach ( $this->get_custom_target_url_condition() as $value => $label ): ?>
                                            <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $rule['cond'] ) ?>>
												<?php echo esc_html( $label ); ?>
                                            </option>
										<?php endforeach; ?>
                                    </select>

                                    <select name="value" class="rule-value"
                                            style="width: 30%" required>
										<?php foreach ( $this->get_custom_target_url_values( $rule['name'] ) as $value => $label ): ?>
                                            <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $rule['value'] ) ?>>
												<?php echo esc_html( $label ); ?>
                                            </option>
										<?php endforeach; ?>
                                    </select>

                                    <input data-repeater-delete type="button"
                                           class="button button-secondary"
                                           value="<?php esc_html_e( 'Delete', 'affiliate-links' ) ?>">
                                </div>
							<?php endforeach; ?>
                        </div>
                        <input data-repeater-create type="button"
                               class="button button-secondary"
                               style="margin-top: 6px"
                               value="<?php esc_html_e( 'And', 'affiliate-links' ) ?>"/>
                    </div>
                    <p style="font-weight: bold"><?php esc_html_e( 'or', 'affiliate-links' ) ?></p>
                </div>
			<?php endforeach; ?>
		<?php endif; ?>
    </div>
    <input data-repeater-create type="button" class="button button-secondary"
           value="<?php esc_html_e( 'Add', 'affiliate-links' ) ?>">
</div>

<script>
    jQuery(document).ready(function ($) {

        $('.sortable').sortable();

        function addRepeaterFields() {
            $(this).slideDown(400, 'swing', function () {
                $(this).find('.rule-name').on('change', function () {
                    var self = this;
                    var data = {
                        'action': aLinkTargetUrl.action,
                        'name': $(this).find('option:selected').val()
                    };
                    $.post(aLinkTargetUrl.ajax_url, data, function (response) {
                        $(self)
                            .siblings('.rule-value')
                            .empty()
                            .append(response)
                            .siblings('.rule-cond')
                            .val(1);

                    });
                });
            });
        }

        function removeFields() {
            $(this).slideUp(function () {
                $(this).remove();
            });
        }

        $('.repeater').repeater({
            defaultValues: {
                'url': '<?php echo $main_target_url  ?>'
            },
            repeaters: [{
                selector: '.inner-repeater',
                show: function () {
                    addRepeaterFields.apply(this);
                },
                hide: function (deleteElement) {
                    removeFields.apply(this);
                },
                isFirstItemUndeletable: true
            }],
            show: function () {
                addRepeaterFields.apply(this);
                $('.sortable').sortable();
            },
            hide: function (deleteElement) {
                removeFields.apply(this);
            },
            ready: function (setIndexes) {
            }
        });
    });
</script>
