<?php
/**
 * @var $this Affiliate_Links_Pro_Stats
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="poststuff" class="af-links-reports-wide">
    <div class="postbox">

		<?php $this->render_view( 'admin-reports-range' ) ?>

        <div class="inside chart-with-sidebar">
            <div class="chart-sidebar">
                <ul class="chart-widgets">
                    <li class="chart-widget">
                        <h4>
                            <span><?php esc_html_e( 'Select Category of Links', 'affiliate-links' ) ?></span>
                        </h4>
                        <div class="section">
                            <form method="GET">
                                <div>
                                    <div class="select2-container enhanced"
                                         style="width:203px;">
                                        <label for="s2id_autogen2"
                                               class="select2-offscreen"></label>
                                        <div class="select2-drop">
                                            <select id="link_cat_id"
                                                    name="link_cat_id"
                                                    style="width: 100%">
                                                <option <?php echo ! $this->get_request_var( 'link_cat_id' ) ? 'selected="selected"' : '' ?>></option>
												<?php foreach ( $this->get_links_cats() as $cat ): ?>
                                                    <option <?php if ( $this->get_request_var( 'link_cat_id' ) == $cat->term_id )
														echo 'selected="selected"' ?>
                                                            value="<?php echo esc_attr( $cat->term_id ) ?>"><?php echo esc_html( $cat->name ); ?>
                                                    </option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                        <input type="submit"
                                               class="submit button"
                                               value="Show">
                                        <input type="hidden" name="post_type"
                                               value="affiliate-links">
                                        <input type="hidden" name="page"
                                               value="reports">
                                        <input type="hidden" name="tab"
                                               value="<?php echo esc_attr( $this->get_current_tab() ) ?>">
                                        <input type="hidden" name="range"
                                               value="<?php echo esc_attr( $this->get_current_range() ) ?>">
										<?php if ( $this->get_request_var( 'start_date' ) ): ?>
                                            <input type="hidden"
                                                   name="start_date"
                                                   value="<?php if ( ! empty( $_GET['start_date'] ) ) {
												       echo esc_attr( $_GET['start_date'] );
											       } ?>">
										<?php endif; ?>
										<?php if ( $this->get_request_var( 'end_date' ) ): ?>
                                            <input type="hidden" name="end_date"
                                                   value="<?php if ( ! empty( $_GET['end_date'] ) ) {
												       echo esc_attr( $_GET['end_date'] );
											       } ?>">
										<?php endif; ?>
                                    </div>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="main">
				<?php if ( $this->get_request_var( 'link_cat_id' ) && count( $this->chart_data ) ): ?>
                    <div id="chart"></div>
				<?php elseif ( $this->get_request_var( 'link_cat_id' ) ): ?>
                    <p class="chart-prompt"><?php esc_html_e( 'There is no activity for the given period.', 'affiliate-links' ); ?></p>
				<?php else: ?>
                    <p class="chart-prompt"><?php esc_html_e( 'Choose a link to view stats', 'affiliate-links' ); ?></p>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
