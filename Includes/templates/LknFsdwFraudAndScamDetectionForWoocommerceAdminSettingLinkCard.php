<?php
if (!defined('ABSPATH')) {
    exit();
}
?>

<div class="linkn-link-settings-card" style="background-image: url('<?php echo esc_url($backgrounds['right']); ?>'), url('<?php echo esc_url($backgrounds['left']); ?>');">
    <div class="linkn-link-logo">
        <div>
            <img src="<?php echo esc_url($logo); ?>" alt="Logo">
        </div>
        <p><?php echo esc_attr($versions); ?></p>
    </div>
    <div class="linkn-link-content">
        <div class="linkn-link-links">
            <div>
                <a target="_blank" href="<?php echo esc_url('https://wordpress.org/plugins/woo-better-shipping-calculator-for-brazil/'); ?>">
                    <b>•</b><?php echo esc_attr__('Documentation', 'payment-gateway-pix-for-woocommerce'); ?>
                </a>
                <a target="_blank" href="<?php echo esc_url('https://www.linknacional.com.br/wordpress/'); ?>">
                    <b>•</b><?php echo esc_attr__('Hosting', 'payment-gateway-pix-for-woocommerce'); ?>
                </a>
            </div>
            <div>
                <a target="_blank" href="<?php echo esc_url('https://www.linknacional.com.br/wordpress/plugins/'); ?>">
                    <b>•</b><?php echo esc_attr__('WP Plugin', 'payment-gateway-pix-for-woocommerce'); ?>
                </a>
                <a target="_blank" href="<?php echo esc_url('https://www.linknacional.com.br/wordpress/suporte/'); ?>">
                    <b>•</b><?php echo esc_attr__('WP Support', 'payment-gateway-pix-for-woocommerce'); ?>
                </a>
            </div>
        </div>
        <div class="linkn-support-links">
            <div class="linkn-stars-div">
                <a target="_blank" href="<?php echo esc_url('https://br.wordpress.org/plugins/woo-better-shipping-calculator-for-brazil/#reviews'); ?>">
                    <p><?php echo esc_attr__('Avaliar o plugin', 'payment-gateway-pix-for-woocommerce'); ?></p>
                    <div class="linkn-stars">
                        <span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
                        <span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
                        <span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
                        <span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
                        <span class="dashicons dashicons-star-filled linkn-stars-icon"></span>
                    </div>
                </a>
            </div>
            <div class="linkn-contact-links">
                <a href="<?php echo esc_url('https://chat.whatsapp.com/IjzHhDXwmzGLDnBfOibJKO'); ?>" target="_blank">
                    <img src="<?php echo esc_url($whatsapp); ?>" alt="Whatsapp Icon" class="linkn-contact-icon">
                </a>
                <a href="<?php echo esc_url('https://t.me/wpprobr'); ?>" target="_blank">
                    <img src="<?php echo esc_url($telegram); ?>" alt="Telegram Icon" class="linkn-contact-icon">
                </a>
            </div>
        </div>
    </div>
</div>