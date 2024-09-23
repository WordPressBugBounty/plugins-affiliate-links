<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <div class="notice notice-warning">
        <p style="font-weight: bold; color: red"><?php esc_html_e( 'This link will be replaced and will no longer be available. Use carefully.', 'affiliate-links' ) ?></p>
    </div>

	<?php if ( ! empty( $this->messages['message'] ) ): ?>
        <div id="message" class="updated">
            <p><?php echo esc_html( $this->messages['message'] ) ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $this->messages['notice'] ) ): ?>
        <div id="notice" class="error">
            <p><?php echo esc_html( $this->messages['notice'] ) ?></p></div>
	<?php endif; ?>

    <h1><?php esc_html_e( 'Link replacer', 'affiliate-links' ) ?></h1>
    <p><?php esc_html_e( 'Using this tool you can do bulk replace old affiliate links with new links in post content.', 'affiliate-links' ) ?></p>
    <div class="form-wrap">
        <form method="post" class="validate">
			<?php wp_nonce_field( 'replace_links', 'replace_links_nonce' ); ?>
            <input type="hidden" name="action" value="replace_links">
            <div class="form-field">
                <label for="current-link"><?php esc_html_e( 'Current Link', 'affiliate-links' ) ?></label>
                <input name="current-link" id="current-link" type="text"
                       value="<?php echo esc_attr( $this->current_link ) ?>"
                       required>
            </div>
            <div class="form-field">
                <label for="new-link"><?php esc_html_e( 'New Link', 'affiliate-links' ) ?></label>
                <input name="new-link" id="new-link" type="text"
                       value="<?php echo esc_attr( $this->new_link ) ?>" required>
                <p><?php esc_html_e( 'This link will be available.', 'affiliate-links' ) ?></p>
            </div>
            <h3 style="color: red; margin-bottom: 0"><?php esc_html_e( 'This action cannot be undone. Please backup your database first.', 'affiliate-links' ) ?></h3>
            <p class="submit"><input type="submit" name="submit" id="submit"
                                     class="button button-red" value="Replace!">
            </p>
        </form>
    </div>
</div>