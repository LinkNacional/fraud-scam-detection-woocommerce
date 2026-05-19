<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="notice notice-warning is-dismissible lkn-fsdw-update-notice" style="padding:14px 16px;border-left-color:#f0b849;">
    <p style="margin:0 0 6px;font-size:14px;">
        <span style="font-size:18px;margin-right:6px;">&#x1F6E1;&#xFE0F;</span>
        <strong><?php esc_html_e( 'Fraud &amp; Scam Detection &#8212; Layout Update', 'fraud-and-scam-detection-for-woocommerce' ); ?></strong>
    </p>
    <p style="margin:0 0 10px;color:#555;">
        <?php esc_html_e( 'The plugin settings page has been updated with a new layout. Please take a moment to review your configuration and confirm that all fields are properly filled in.', 'fraud-and-scam-detection-for-woocommerce' ); ?>
    </p>
    <p style="margin:0;">
        <a href="<?php echo esc_url( $settings_url ); ?>" class="button button-primary" style="display:inline-flex;align-items:center;gap:6px;">
            <span>&#x2699;&#xFE0F;</span>
            <?php esc_html_e( 'Check Settings', 'fraud-and-scam-detection-for-woocommerce' ); ?>
        </a>
    </p>
</div>
