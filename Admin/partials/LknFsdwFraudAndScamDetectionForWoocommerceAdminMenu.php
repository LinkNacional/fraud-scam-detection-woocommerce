<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Admin\partials;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registra o menu lateral "AntiFraud" e seus atalhos para as abas da
 * página de configurações do WooCommerce.
 *
 * O redirect para a aba alvo é feito no hook `admin_init` (antes de qualquer
 * saída HTML). Os callbacks das páginas existem apenas para que o WordPress
 * gere as URLs corretas (admin.php?page=<slug>) — eles nunca chegam a rodar
 * porque o redirect de `admin_init` encerra a requisição antes.
 */
class LknFsdwFraudAndScamDetectionForWoocommerceAdminMenu
{
    private const CAPABILITY   = 'manage_woocommerce';
    private const MENU_SLUG    = 'lkn-fsdw-antifraud';
    private const SETTINGS_URL = 'admin.php?page=wc-settings&tab=lkn_anti_fraud';

    /**
     * Hook: admin_menu
     */
    public function register(): void
    {
        $title = __('AntiFraud', 'fraud-and-scam-detection-for-woocommerce');

        add_menu_page(
            $title,
            $title,
            self::CAPABILITY,
            self::MENU_SLUG,
            array($this, 'redirect'),
            'dashicons-shield-alt',
            55
        );

        // Primeiro item clona o slug do pai (evita a entrada duplicada do WP)
        // e aponta para a aba principal (Captcha). O callback já está
        // registrado pelo add_menu_page acima.
        add_submenu_page(
            self::MENU_SLUG,
            __('Captcha', 'fraud-and-scam-detection-for-woocommerce'),
            __('Captcha', 'fraud-and-scam-detection-for-woocommerce'),
            self::CAPABILITY,
            self::MENU_SLUG
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Data Blocking', 'fraud-and-scam-detection-for-woocommerce'),
            __('Data Blocking', 'fraud-and-scam-detection-for-woocommerce'),
            self::CAPABILITY,
            self::MENU_SLUG . '-data-blocking',
            array($this, 'redirect')
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Banned IPs', 'fraud-and-scam-detection-for-woocommerce'),
            __('Banned IPs', 'fraud-and-scam-detection-for-woocommerce'),
            self::CAPABILITY,
            self::MENU_SLUG . '-banned-ips',
            array($this, 'redirect')
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Blocked Data', 'fraud-and-scam-detection-for-woocommerce'),
            __('Blocked Data', 'fraud-and-scam-detection-for-woocommerce'),
            self::CAPABILITY,
            self::MENU_SLUG . '-block-by-data',
            array($this, 'redirect')
        );
    }

    /**
     * Redireciona os atalhos do menu para a página de configurações do
     * WooCommerce já na aba alvo.
     *
     * Hooks: admin_init (efetivo) e os callbacks das páginas do menu.
     */
    public function redirect(): void
    {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        $tabs = array(
            self::MENU_SLUG                    => 'captcha',
            self::MENU_SLUG . '-data-blocking' => 'antifraud',
            self::MENU_SLUG . '-banned-ips'    => 'banned-ips',
            self::MENU_SLUG . '-block-by-data' => 'block-by-data',
        );

        if (!isset($tabs[$page]) || !current_user_can(self::CAPABILITY)) {
            return;
        }

        $url = admin_url(self::SETTINGS_URL . '&lkn_fsdw_tab=' . $tabs[$page]);

        // No `admin_init` ainda não houve saída; o fallback cobre o cenário
        // improvável de o callback da página ser executado após o header.
        if (headers_sent()) {
            printf('<meta http-equiv="refresh" content="0;url=%s">', esc_url($url));
            exit;
        }

        wp_safe_redirect($url);
        exit;
    }
}
