<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="wrap">

    <?php if ( ! empty( $this->messages['message'] ) ): ?>
        <div id="message" class="error">
            <p><?php echo esc_html( $this->messages['message'] ) ?></p></div>
    <?php endif; ?>

    <?php if ( ! empty( $this->messages['success'] ) ): ?>
        <div id="message" class="updated">
            <p><?php echo esc_html( $this->messages['success'] ) ?></p></div>
    <?php endif; ?>

    <h1><?php esc_html_e( 'Import export', 'affiliate-links' ) ?></h1>
    <p><?php esc_html_e( 'Using this tool you can import or export all affilate links and categories for them.', 'affiliate-links' ) ?></p>
    <div class="form-wrap">
        <form enctype="multipart/form-data" method="post" class="validate">
            <?php wp_nonce_field( 'import', 'file_nonce' ); ?>
            <input type="hidden" name="action" value="import">
            <div class="form-field">
                <label for="current-link"><?php esc_html_e( 'Upload CSV or XML file', 'affiliate-links' ) ?></label>
                <input name="file" id="current-link" type="file"
                       required>
            </div>
            <p class="submit"><input type="submit" name="submit"
                                     class="button button-red" value="import">
            </p>
        </form>
    </div>
    <div class="form-wrap">
         <form method="post" class="validate">
            <?php wp_nonce_field( 'export', 'export_nonce' ); ?>
            <p><?php esc_html_e( 'Please select your preferred file type for export', 'affiliate-links' ); ?>:</p>
            <div>
                <p><input type="radio" id="export_file_type_csv" name="export_file_type" value="csv" checked="checked" /><?php esc_html_e( 'CSV', 'affiliate-links' ); ?></p>
                <p><input type="radio" id="export_file_type_xml" name="export_file_type" value="xml" /><?php esc_html_e( 'XML', 'affiliate-links' ); ?></p>
            </div>
            <input type="hidden" name="action" value="export">
            <p class="submit"><input type="submit" name="submit" class="button button-red" value="export">
            </p>
    </div>
</div>