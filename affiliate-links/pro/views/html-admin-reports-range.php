<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>
<div class="stats_range">
    <ul>
		<?php foreach ( $this->get_ranges() as $range => $name ): ?>
            <li class="<?php echo( $this->get_current_range() == $range ? 'active' : '' ) ?>">
                <a href="<?php echo esc_url( remove_query_arg( array(
					'start_date',
					'end_date',
				), add_query_arg( 'range', $range ) ) ) ?>">
					<?php echo esc_html( $name ) ?>
                </a>
            </li>
		<?php endforeach; ?>
        <li class="custom <?php echo $this->get_current_range() == 'custom' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Custom:', 'affiliate-links' ); ?>
            <form method="GET">
                <div>
					<?php foreach ( $_GET as $key => $value ): ?>
						<?php if ( is_array( $value ) ): ?>
							<?php foreach ( $value as $v ): ?>
                                <input type="hidden"
                                       name="<?php echo esc_attr( sanitize_text_field( $key ) ) . '[]'; ?>"
                                       value="<?php echo esc_attr( sanitize_text_field( $v ) ) ?>"/>'
							<?php endforeach; ?>
						<?php else: ?>
                            <input type="hidden"
                                   name="<?php echo esc_attr( sanitize_text_field( $key ) ) ?>"
                                   value="<?php echo esc_attr( sanitize_text_field( $value ) ) ?>"/>
						<?php endif; ?>
					<?php endforeach; ?>

                    <input type="hidden" name="range" value="custom"/>

                    <input type="text" size="9" placeholder="yyyy-mm-dd"
                           value="<?php if ( ! empty( $_GET['start_date'] ) ) {
						       echo esc_attr( $_GET['start_date'] );
					       } ?>"
                           name="start_date" class="range_datepicker from"/>

                    <input type="text" size="9" placeholder="yyyy-mm-dd"
                           value="<?php if ( ! empty( $_GET['end_date'] ) ) {
						       echo esc_attr( $_GET['end_date'] );
					       } ?>"
                           name="end_date" class="range_datepicker to"/>

                    <input type="submit" class="button"
                           value="<?php esc_attr_e( 'Go', 'affiliate-links' ); ?>"/>
                </div>
            </form>
        </li>
    </ul>
</div>