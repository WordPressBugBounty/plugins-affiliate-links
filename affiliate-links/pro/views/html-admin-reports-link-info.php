<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * @var $this  Affiliate_Links_Pro_Stats
 */
?>

<!-- browser statistic -->
<?php if ( count( $this->get_link_browsers_data() ) ): ?>
    <table cellpadding="3" cellspacing="2"
           style="float: left; margin: 20px 10px 10px 0;">
        <tbody>
        <tr class="alternate">
            <th><?php esc_html_e( 'Browser', 'affiliate-links' ) ?></th>
            <th><?php esc_html_e( 'Sessions', 'affiliate-links' ) ?></th>
        </tr>
		<?php foreach ( $this->get_link_browsers_data() as $browser ): ?>
            <tr class="alternate">
                <td><?php echo esc_html( AFL_PRO()->get_browser_title( $browser['name'] ) ) ?></td>
                <td class="stats-number"><?php echo esc_html( $browser['hits'] ) ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


<!-- os statistic -->
<?php if ( count( $this->get_link_data( 'os' ) ) ): ?>
    <table cellpadding="3" cellspacing="2"
           style="float: left; margin: 20px 10px 10px 0;">
        <tbody>
        <tr class="alternate">
            <th><?php esc_html_e( 'OS', 'affiliate-links' ) ?></th>
            <th><?php esc_html_e( 'Hits', 'affiliate-links' ) ?></th>
        </tr>
		<?php foreach ( $this->get_link_data( 'os' ) as $link ): ?>
            <tr class="alternate">
                <td><?php echo esc_html( $link['os'] ) ?></td>
                <td class="stats-number"><?php echo esc_html( $link['hits'] ) ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- platform statistic -->
<?php if ( count( $this->get_link_data( 'platform' ) ) ): ?>
    <table cellpadding="3" cellspacing="2"
           style="float: left; margin: 20px 10px 10px 0;">
        <tbody>
        <tr class="alternate">
            <th><?php esc_html_e( 'Platform', 'affiliate-links' ) ?></th>
            <th><?php esc_html_e( 'Hits', 'affiliate-links' ) ?></th>
        </tr>
		<?php foreach ( $this->get_link_data( 'platform' ) as $link ): ?>
            <tr class="alternate">
                <td><?php echo esc_html( $link['platform'] ) ?></td>
                <td class="stats-number"><?php echo esc_html( $link['hits'] ) ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- language statistic -->
<?php if ( count( $this->get_link_data( 'lang' ) ) ): ?>
    <table cellpadding="3" cellspacing="2"
           style="float: left; margin: 20px 10px 10px 0;">
        <tbody>
        <tr class="alternate">
            <th><?php esc_html_e( 'Language', 'affiliate-links' ) ?></th>
            <th><?php esc_html_e( 'Hits', 'affiliate-links' ) ?></th>
        </tr>
		<?php foreach ( $this->get_link_data( 'lang' ) as $link ): ?>
            <tr class="alternate">
                <td><?php echo esc_html( $link['lang'] ) ?></td>
                <td class="stats-number"><?php echo esc_html( $link['hits'] ) ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- referrer statistic -->
<?php if ( count( $this->get_link_data( 'referer' ) ) ): ?>
    <table cellpadding="3" cellspacing="2"
           style="float: left; margin: 20px 10px 10px 0;">
        <tbody>
        <tr class="alternate">
            <th><?php esc_html_e( 'Referrer link', 'affiliate-links' ) ?></th>
            <th><?php esc_html_e( 'Hits', 'affiliate-links' ) ?></th>
        </tr>
		<?php foreach ( $this->get_link_data( 'referer' ) as $link ): ?>
            <tr class="alternate">
                <td>
					<?php if ( 'Direct Entry' == $link['referer'] ): ?>
						<?php echo esc_html( $link['referer'] ) ?>
					<?php else: ?>
                        <a href="<?php echo esc_url( $link['referer'] ) ?>"><?php echo esc_url( $link['referer'] ) ?></a>
					<?php endif; ?>
                </td>
                <td class="stats-number"><?php echo esc_html( $link['hits'] ) ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
