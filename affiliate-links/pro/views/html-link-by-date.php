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
                        <h4><span><?php esc_html_e( 'Select Link', 'affiliate-links' ) ?></span></h4>
                        <div class="section">
                            <form method="GET">
                                <div>
                                    <div class="select2-container enhanced"
                                         style="width:203px;">
                                        <label for="links"
                                               class="select2-offscreen"></label>
                                        <div class="select2-drop">
                                            <select id="links" name="link_id"
                                                    style="width: 100%">
                                                <option <?php echo ! $this->get_request_var( 'link_id' ) ? 'selected="selected"' : '' ?>></option>
												<?php foreach ( $this->get_links() as $link ): ?>
                                                    <option <?php if ( $this->get_request_var( 'link_id' ) == $link->ID )
														echo 'selected="selected"' ?>
                                                            value="<?php echo esc_attr( $link->ID ) ?>"><?php echo esc_html( $link->post_title ); ?>
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
                <div class="chart-container">
					<?php if ( $this->get_request_var( 'link_id' ) && count( $this->chart_data ) ): ?>
                        <div id="chart"></div>
						<?php $this->render_view( 'admin-reports-link-info', FALSE ) ?>
					<?php elseif ( $this->get_request_var( 'link_id' ) ): ?>
                        <p class="chart-prompt"><?php esc_html_e( 'There is no activity for the given period.', 'affiliate-links' ); ?></p>
					<?php else: ?>
                        <p class="chart-prompt"><?php esc_html_e( 'Choose a link to view stats', 'affiliate-links' ); ?></p>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
