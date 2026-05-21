<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Admin\partials;

if (!defined('ABSPATH')) {
    exit;
}

class LknFsdwFraudAndScamDetectionForWoocommerceSettingsPage extends \WC_Settings_Page
{
    public $id;
    public $method_title;
    public $method_description;

    public function __construct()
    {
        $this->id    = 'lkn_anti_fraud';
        $this->label = __('Antifraude', 'fraud-and-scam-detection-for-woocommerce');
        $this->method_title       = esc_attr__('Detecção de Fraudes e Golpes', 'fraud-and-scam-detection-for-woocommerce');
        $this->method_description = esc_attr__('Configure as opções de proteção antifraude e integração com reCAPTCHA para maior segurança nas transações.', 'fraud-and-scam-detection-for-woocommerce');
        parent::__construct();
    }

    public function get_settings()
    {
        $settings = array(

            /* ── Bloco principal ─────────────────────────────────── */
            'section_title' => array(
                'title'             => __('Antifraud', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'title',
                'id'                => 'lkn_anti_fraud_section_title',
                'block_id'          => 'antifraud',
                'description'       => '',
                'default'           => '',
                'desc_tip'          => false,
                'block_title'       => __('Antifraud', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('General antifraud protection settings.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => '',
            ),
            'enable_recaptcha' => array(
                'title'             => __('Enable Antifraud', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'checkbox',
                'id'                => 'lknFraudDetectionForWoocommerceEnableRecaptcha',
                'label'             => __('Enable antifraud protection during checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'default'           => 'no',
                'description'       => __('Enable antifraud protection during checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Enable Antifraud', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Activate protection at checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Enable to require security validation at checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'ip_block_behavior' => array(
                'title'             => __('IP Block Behavior', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'multicheck',
                'id'                => 'lknFraudDetectionForWoocommerceIpBlockBehavior',
                'options'           => array(
                    'block_order' => __('Block Order (do not allow the order to be placed)', 'fraud-and-scam-detection-for-woocommerce'),
                    'mark_fraud'  => __('Mark Order as Fraud', 'fraud-and-scam-detection-for-woocommerce'),
                    'add_note'    => __('Add Note to Order Only', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'options_checked'   => array(
                    'block_order' => 'yes',
                    'mark_fraud'  => 'yes',
                    'add_note'    => 'yes',
                ),
                'options_input_description' => array(
                    'block_order' => __('Prevents the order from being placed when a banned IP is detected.', 'fraud-and-scam-detection-for-woocommerce'),
                    'mark_fraud'  => __('Marks the order status as fraudulent for review.', 'fraud-and-scam-detection-for-woocommerce'),
                    'add_note'    => __('Adds a note to the order without blocking it.', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'default'           => 'yes',
                'description'       => __('Choose how the system should behave when a banned IP attempts to place an order.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('IP Block Behavior', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Define the action taken when a banned IP tries to checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'ban_duration' => array(
                'title'             => __('Ban Duration', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'number',
                'id'                => 'lknFraudDetectionForWoocommerceBanDuration',
                'default'           => '0',
                'description'       => __('How long each ban lasts. Use 0 with unit "Forever" for permanent bans.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Ban Duration', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('How long each ban lasts.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Enter the numeric duration. Pair with the unit field below.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(
                    'min'  => '0',
                    'step' => '1',
                ),
            ),
            'ban_duration_unit' => array(
                'title'             => __('Ban Duration Unit', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'select',
                'id'                => 'lknFraudDetectionForWoocommerceBanDurationUnit',
                'options'           => array(
                    'forever' => __('Forever', 'fraud-and-scam-detection-for-woocommerce'),
                    'hours'   => __('Hours', 'fraud-and-scam-detection-for-woocommerce'),
                    'days'    => __('Days', 'fraud-and-scam-detection-for-woocommerce'),
                    'weeks'   => __('Weeks', 'fraud-and-scam-detection-for-woocommerce'),
                    'months'  => __('Months', 'fraud-and-scam-detection-for-woocommerce'),
                    'years'   => __('Years', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'default'           => get_option( 'lknFraudDetectionForWoocommerceBanDurationUnit', 'forever' ),
                'description'       => __('Time unit for the ban duration. Select "Forever" for permanent bans.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Ban Duration Unit', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Select the unit of time for the ban.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('When "Forever" is selected, the duration field is disabled.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
                'join'              => 'ban_duration',
            ),
            'enable_ip_lookup' => array(
                'title'             => __('IP Verify', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'checkbox',
                'id'                => 'lknFraudDetectionForWoocommerceEnableIpLookup',
                'label'             => __('Enable IP location lookup link on the order detail page.', 'fraud-and-scam-detection-for-woocommerce'),
                'default'           => 'yes',
                'description'       => __('Show IP lookup, filter and ban actions on the order page.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('IP Verification', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Show IP lookup, filter and ban actions on the order page.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Enables an IP location lookup link on the order detail page.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'enable_ip_filter' => array(
                'title'             => __('Filter Orders by IP', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'checkbox',
                'id'                => 'lknFraudDetectionForWoocommerceEnableIpFilter',
                'label'             => __('Enable a link to filter orders by the customer IP on the order detail page.', 'fraud-and-scam-detection-for-woocommerce'),
                'default'           => 'yes',
                'description'       => __('Show a filter link to list orders by the same IP on the order page.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Filter Orders by IP', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('List all orders placed from the same IP.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Enables a link to filter orders by the customer IP on the order detail page.', 'fraud-and-scam-detection-for-woocommerce'),
                'join'              => 'enable_ip_lookup',
                'custom_attributes' => array(),
            ),
            'enable_ip_ban' => array(
                'title'             => __('Ban IPs', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'checkbox',
                'id'                => 'lknFraudDetectionForWoocommerceEnableIpBan',
                'label'             => __('Enable ban/unban IP link on the order detail page.', 'fraud-and-scam-detection-for-woocommerce'),
                'default'           => 'yes',
                'description'       => __('Show a ban/unban link to block IPs from checkout on the order page.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Ban IPs', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Block suspicious IPs from completing checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Enables a ban/unban IP link on the order detail page.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_warning'     => __('<strong>⚠ Important:</strong> For this feature to correctly identify the customer\'s real IP, your server proxy settings must be properly configured (e.g. trusted proxies / X-Forwarded-For headers). If you are unsure, contact your server administrator before enabling this option.', 'fraud-and-scam-detection-for-woocommerce'),
                'join'              => 'enable_ip_lookup',
                'custom_attributes' => array(),
            ),
            'enable_data_block' => array(
                'title'                     => __('Enable Data Blocking', 'fraud-and-scam-detection-for-woocommerce'),
                'type'                      => 'multicheck',
                'id'                        => 'lknFraudDetectionForWoocommerceEnableDataBlock',
                'options'                   => array(
                    'email'           => __('Block by Email', 'fraud-and-scam-detection-for-woocommerce'),
                    'email_domain'    => __('Block by Email Domain', 'fraud-and-scam-detection-for-woocommerce'),
                    'phone'           => __('Block by Phone', 'fraud-and-scam-detection-for-woocommerce'),
                    'country'         => __('Block by Country', 'fraud-and-scam-detection-for-woocommerce'),
                    'device_identity' => __('Block by Device Identity', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'options_checked'           => array(
                    'email'           => 'no',
                    'email_domain'    => 'no',
                    'phone'           => 'no',
                    'country'         => 'no',
                    'device_identity' => 'no',
                ),
                'options_input_description' => array(
                    'email'           => __('Block orders from specific email addresses. Manage blocked emails in the list below.', 'fraud-and-scam-detection-for-woocommerce'),
                    'email_domain'    => __('Block orders from specific email domains. Manage blocked domains in the list below.', 'fraud-and-scam-detection-for-woocommerce'),
                    'phone'           => __('Block orders from specific phone numbers. Manage blocked phones in the list below.', 'fraud-and-scam-detection-for-woocommerce'),
                    'country'         => __('Block orders from specific countries. Use 2-letter ISO codes (e.g. BR, US). Manage the list below.', 'fraud-and-scam-detection-for-woocommerce'),
                    'device_identity' => __('Block orders from specific device fingerprints. Fingerprints are collected at checkout — copy them from order details to block a device.', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'default'                   => 'no',
                'description'               => __('When enabled, orders are checked against the blocked data lists.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'                  => true,
                'block_title'               => __('Enable Data Blocking', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'           => __('Select which data types should block orders at checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes'         => array(),
            ),
            'security_version' => array(
                'title'             => __('Security Version', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'select',
                'id'                => 'lknFraudDetectionForWoocommerceRecaptchaSelected',
                'options'           => array(
                    'none'                => __('None', 'fraud-and-scam-detection-for-woocommerce'),
                    'googleRecaptchaV3'   => __('Google reCAPTCHA V3', 'fraud-and-scam-detection-for-woocommerce'),
                    'cloudflareTurnstile' => __('Cloudflare Turnstile', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'default'           => get_option( 'lknFraudDetectionForWoocommerceRecaptchaSelected', 'googleRecaptchaV3' ),
                'description'       => __('Select the security service to use at checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Security Version', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Choose between Google reCAPTCHA or Cloudflare Turnstile.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Select the provider for checkout validation.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'debug' => array(
                'title'             => __('Debug', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'checkbox',
                'id'                => 'lknFraudDetectionForWoocommerceDebug',
                'label'             => __('Enable debug logs.', 'fraud-and-scam-detection-for-woocommerce'),
                'default'           => 'no',
                'description'       => sprintf(
                    /* translators: %s: URL to WooCommerce logs page. */
                    __( 'Enable debug logs <a href="%s" target="_blank">View logs</a>', 'fraud-and-scam-detection-for-woocommerce' ),
                    esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) )
                ),
                'desc_tip'          => true,
                'block_title'       => __('Debug', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Enable to record debug logs.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Logs can be viewed in the WooCommerce status area.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),

            /* ── Google reCAPTCHA ────────────────────────────────── */
            'google_section_title' => array(
                'title'             => __('Google reCAPTCHA', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'title',
                'id'                => 'lkn_anti_fraud_google_section_title',
                'block_id'          => 'google-recaptcha',
                'description'       => '',
                'default'           => '',
                'desc_tip'          => false,
                'block_title'       => __('Google reCAPTCHA', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Configure your Google reCAPTCHA V3 credentials.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => '',
            ),
            'recaptcha_keys_info' => array(
                'title'             => __('Generate Google reCAPTCHA V3 Keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'url',
                'id'                => 'lknFraudDetectionForWoocommerceRecaptchaKeysInfo',
                'default'           => 'https://www.google.com/recaptcha/admin/',
                'label'             => __('Generate Google reCAPTCHA V3 Keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'description'       => __('Click to access the Google reCAPTCHA panel and generate your integration keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Generate Google reCAPTCHA V3 Keys', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Click to generate new keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Get your Google reCAPTCHA V3 keys.', 'fraud-and-scam-detection-for-woocommerce'),
            ),
            'recaptcha_site_key' => array(
                'title'             => __('reCAPTCHA Site Key', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'text',
                'id'                => 'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Key',
                'default'           => '',
                'description'       => __('Google reCAPTCHA V3 service key.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('reCAPTCHA Site Key', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Enter your Google reCAPTCHA V3 site key.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Google reCAPTCHA V3 public key.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'recaptcha_secret_key' => array(
                'title'             => __('reCAPTCHA Secret Key', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'text',
                'id'                => 'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Secret',
                'default'           => '',
                'description'       => __('Google reCAPTCHA V3 secret key.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('reCAPTCHA Secret Key', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Enter your Google reCAPTCHA V3 secret key.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Google reCAPTCHA V3 private key.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'recaptcha_score' => array(
                'title'             => __('Minimum Score', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'number',
                'id'                => 'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score',
                'default'           => '0.5',
                'description'       => __('The minimum score validated by reCAPTCHA for payment acceptance. Range: 0 to 1. It is recommended to use a score above 0.7.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Minimum Score', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Set the minimum score for approval.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Value between 0 and 1. Recommended above 0.7.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(
                    'step' => '0.1',
                    'min'  => '0',
                    'max'  => '1',
                ),
            ),

            /* ── Cloudflare Turnstile ────────────────────────────── */
            'cloudflare_section_title' => array(
                'title'             => __('Cloudflare Turnstile', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'title',
                'id'                => 'lkn_anti_fraud_cloudflare_section_title',
                'block_id'          => 'cloudflare-turnstile',
                'description'       => '',
                'default'           => '',
                'desc_tip'          => false,
                'block_title'       => __('Cloudflare Turnstile', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Configure your Cloudflare Turnstile credentials.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => '',
            ),
            'cloudflare_keys_info' => array(
                'title'             => __('Generate Cloudflare Turnstile Keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'url',
                'id'                => 'lknFraudDetectionForWoocommerceCloudflareTurnstileKeysInfo',
                'default'           => 'https://dash.cloudflare.com/?to=/:account/turnstile',
                'label'             => __('Generate Cloudflare Turnstile Keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'description'       => __('Click to access the Cloudflare dashboard and generate your Turnstile site and secret keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Generate Cloudflare Turnstile Keys', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Click to generate new keys.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Get your Cloudflare Turnstile keys.', 'fraud-and-scam-detection-for-woocommerce'),
            ),
            'cloudflare_site_key' => array(
                'title'             => __('Turnstile Site Key', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'text',
                'id'                => 'lknFraudDetectionForWoocommerceCloudflareTurnstileSiteKey',
                'default'           => '',
                'description'       => __('Cloudflare Turnstile public site key.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Turnstile Site Key', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Enter your Cloudflare Turnstile site key.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Cloudflare Turnstile public key.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'cloudflare_secret_key' => array(
                'title'             => __('Turnstile Secret Key', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'text',
                'id'                => 'lknFraudDetectionForWoocommerceCloudflareTurnstileSecretKey',
                'default'           => '',
                'description'       => __('Cloudflare Turnstile secret key.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Turnstile Secret Key', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Enter your Cloudflare Turnstile secret key.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Cloudflare Turnstile private key.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),
            'cloudflare_theme' => array(
                'title'             => __('Turnstile Theme', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'select',
                'id'                => 'lknFraudDetectionForWoocommerceCloudflareTurnstileTheme',
                'options'           => array(
                    'auto'  => __('Auto', 'fraud-and-scam-detection-for-woocommerce'),
                    'light' => __('Light', 'fraud-and-scam-detection-for-woocommerce'),
                    'dark'  => __('Dark', 'fraud-and-scam-detection-for-woocommerce'),
                ),
                'default'           => get_option( 'lknFraudDetectionForWoocommerceCloudflareTurnstileTheme', 'light' ),
                'description'       => __('Choose the visual theme of the Turnstile widget.', 'fraud-and-scam-detection-for-woocommerce'),
                'desc_tip'          => true,
                'block_title'       => __('Turnstile Theme', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Select light, dark or auto.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => __('Visual appearance of the Turnstile widget.', 'fraud-and-scam-detection-for-woocommerce'),
                'custom_attributes' => array(),
            ),

            /* ── Banned IPs ──────────────────────────────────────── */
            'banned_ips_section_title' => array(
                'title'             => __('Banned IPs', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'title',
                'id'                => 'lkn_anti_fraud_banned_ips_section_title',
                'block_id'          => 'banned-ips',
                'description'       => '',
                'default'           => '',
                'desc_tip'          => false,
                'block_title'       => __('Banned IPs', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('List of IP addresses blocked from checkout.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => '',
            ),

            /* ── Block by Data ───────────────────────────────────── */
            'blocked_data_section_title' => array(
                'title'             => __('Block by Data', 'fraud-and-scam-detection-for-woocommerce'),
                'type'              => 'title',
                'id'                => 'lkn_anti_fraud_blocked_data_section_title',
                'block_id'          => 'block-by-data',
                'description'       => '',
                'default'           => '',
                'desc_tip'          => false,
                'block_title'       => __('Block by Data', 'fraud-and-scam-detection-for-woocommerce'),
                'block_sub_title'   => __('Manage blocked emails, phone numbers, countries and device identities.', 'fraud-and-scam-detection-for-woocommerce'),
                'input_description' => '',
            ),

        );
        return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
    }

    public function output() {
        $this->admin_options();
    }

    public function save()
    {
        $settings = $this->get_settings();
        \WC_Admin_Settings::save_fields($settings);
    }

    public function admin_options()
    {
        // Dados necessários para o template sem dependências externas
        $plugin_path = 'invoice-payment-for-woocommerce/wc-invoice-payment.php';
        $invoice_plugin_installed = file_exists(WP_PLUGIN_DIR . '/' . $plugin_path);
        $template_path = plugin_dir_path(dirname(__DIR__)) . 'Includes/templates/';

        wp_enqueue_style( 'lknFraudDetectionForWoocommerceAdminSettings', FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL . 'Admin/css/lknFraudDetectionForWoocommerceAdminSettings.css', array(), FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION, 'all' );
        wp_enqueue_style( 'lknFraudDetectionForWoocommerceAdminSettingLinkCard', FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL . 'Admin/css/lknFraudDetectionForWoocommerceAdminSettingLinkCard.css', array(), FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION, 'all' );
        wp_enqueue_style( 'lknFraudDetectionForWoocommerceAdminToggleFields', FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL . 'Admin/css/lknFraudDetectionForWoocommerceAdminToggleFields.css', array(), FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION, 'all' );

        wp_enqueue_script(
            'lkn-fraud-detection-for-woocommerce-admin-save-fields',
            plugin_dir_url( __FILE__ ) . '../js/compiled/lknFraudDetectionForWoocommerceAdminSaveFields.COMPILED.js',
            array('jquery'),
            FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
            true
        );

        // Pass dynamic settings to JS (generic, not gateway-specific)
        $settings_prefix = $this->id;
        $ajax_action = $this->id . '_save_settings';
        $settings_nonce = wp_create_nonce($ajax_action);
        wp_localize_script(
            'lkn-fraud-detection-for-woocommerce-admin-save-fields',
            'lknAntiFraudSettings',
            array(
                'settingsPrefix' => $settings_prefix,
                'ajaxAction'     => $ajax_action,
                'settingsNonce'  => $settings_nonce,
                'ajaxUrl'        => admin_url('admin-ajax.php'),
            )
        );

        // Localize score messages for the minimum score script
        $score_messages = array(
            'scoreBetween0and3'   => __('High likelihood of automated (bot) behavior.', 'fraud-and-scam-detection-for-woocommerce'),
            'scoreBetween4and5'   => __('Intermediate behavior.', 'fraud-and-scam-detection-for-woocommerce'),
            'scoreBetween6and7'   => __('Behavior generally human, but with some uncertainty.', 'fraud-and-scam-detection-for-woocommerce'),
            'scoreBetween8and10'  => __('High likelihood of legitimate human behavior.', 'fraud-and-scam-detection-for-woocommerce'),
        );
        wp_enqueue_script(
            'lkn-fraud-detection-for-woocommerce-admin-minimum-score',
            plugin_dir_url( __FILE__ ) . '../js/lknFraudDetectionForWoocommerceAdminMinimumScore.js',
            array('jquery'),
            FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
            true
        );
        wp_localize_script(
            'lkn-fraud-detection-for-woocommerce-admin-minimum-score',
            'lknAntiFraudScoreMessages',
            $score_messages
        );


        wp_enqueue_script(
            'lkn-fraud-detection-for-woocommerce-admin-tabs',
            plugin_dir_url( __FILE__ ) . '../js/lknFraudDetectionForWoocommerceAdminTabs.js',
            array('jquery'),
            FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
            true
        );

        wp_enqueue_script(
            'lkn-fraud-detection-for-woocommerce-admin-banned-ips',
            plugin_dir_url( __FILE__ ) . '../js/compiled/lknFraudDetectionForWoocommerceAdminBannedIps.COMPILED.js',
            array('jquery'),
            FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
            true
        );
        wp_localize_script(
            'lkn-fraud-detection-for-woocommerce-admin-banned-ips',
            'lknFsdwBannedIpsVars',
            array(
                'ajaxUrl'    => admin_url('admin-ajax.php'),
                'nonceGet'   => wp_create_nonce('lkn_fsdw_get_banned_ips'),
                'nonceBan'   => wp_create_nonce('lkn_fsdw_ban_ip'),
                'nonceUnban' => wp_create_nonce('lkn_fsdw_unban_ip'),
                'i18n'       => array(
                    'placeholder'    => __('IPv4 or IPv6', 'fraud-and-scam-detection-for-woocommerce'),
                    'banBtn'         => __('Ban IP', 'fraud-and-scam-detection-for-woocommerce'),
                    'unbanBtn'       => __('Unban', 'fraud-and-scam-detection-for-woocommerce'),
                    'colIp'          => __('IP Address', 'fraud-and-scam-detection-for-woocommerce'),
                    'colBannedBy'    => __('Banned By', 'fraud-and-scam-detection-for-woocommerce'),
                    'colBannedAt'    => __('Banned At', 'fraud-and-scam-detection-for-woocommerce'),
                    'colExpiresAt'   => __('Expires At', 'fraud-and-scam-detection-for-woocommerce'),
                    'forever'        => __('Forever', 'fraud-and-scam-detection-for-woocommerce'),
                    'colActions'     => __('Actions', 'fraud-and-scam-detection-for-woocommerce'),
                    'loading'        => __('Loading…', 'fraud-and-scam-detection-for-woocommerce'),
                    'empty'          => __('No banned IPs.', 'fraud-and-scam-detection-for-woocommerce'),
                    'cancel'         => __('Cancel', 'fraud-and-scam-detection-for-woocommerce'),
                    'banTitle'       => __('Ban IP', 'fraud-and-scam-detection-for-woocommerce'),
                    'banConfirmMsg'  => __('Do you want to ban the following IP from checkout?', 'fraud-and-scam-detection-for-woocommerce'),
                    'banConfirmBtn'  => __('Confirm Ban', 'fraud-and-scam-detection-for-woocommerce'),
                    'unbanTitle'     => __('Unban IP', 'fraud-and-scam-detection-for-woocommerce'),
                    'unbanConfirmMsg'=> __('Do you want to unban the following IP?', 'fraud-and-scam-detection-for-woocommerce'),
                    'unbanConfirmBtn'=> __('Confirm Unban', 'fraud-and-scam-detection-for-woocommerce'),
                    'successBan'     => __('IP banned successfully.', 'fraud-and-scam-detection-for-woocommerce'),
                    'successUnban'   => __('IP unbanned successfully.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorLoad'      => __('Failed to load IPs.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorBan'       => __('Error banning IP.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorUnban'     => __('Error unbanning IP.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorEmpty'     => __('Enter an IP address.', 'fraud-and-scam-detection-for-woocommerce'),
                ),
            )
        );

        wp_enqueue_script(
            'lkn-fraud-detection-for-woocommerce-admin-toggle-fields',
            plugin_dir_url( __FILE__ ) . '../js/lknFraudDetectionForWoocommerceAdminToggleFields.js',
            array('jquery'),
            FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
            true
        );

        wp_enqueue_script(
            'lkn-fraud-detection-for-woocommerce-admin-blocked-data',
            plugin_dir_url( __FILE__ ) . '../js/compiled/lknFraudDetectionForWoocommerceAdminBlockedData.COMPILED.js',
            array('jquery'),
            FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
            true
        );
        wp_localize_script(
            'lkn-fraud-detection-for-woocommerce-admin-blocked-data',
            'lknFsdwBlockedDataVars',
            array(
                'ajaxUrl'     => admin_url('admin-ajax.php'),
                'nonceGet'    => wp_create_nonce('lkn_fsdw_get_blocked_data'),
                'nonceAdd'    => wp_create_nonce('lkn_fsdw_add_blocked_data'),
                'nonceRemove' => wp_create_nonce('lkn_fsdw_remove_blocked_data'),
                'i18n'        => array(
                    'tabEmail'                  => __('Emails', 'fraud-and-scam-detection-for-woocommerce'),
                    'tabEmailDomain'            => __('Email Domains', 'fraud-and-scam-detection-for-woocommerce'),
                    'tabPhone'                  => __('Phones', 'fraud-and-scam-detection-for-woocommerce'),
                    'tabCountry'                => __('Countries', 'fraud-and-scam-detection-for-woocommerce'),
                    'tabDeviceIdentity'         => __('Device Identities', 'fraud-and-scam-detection-for-woocommerce'),
                    'placeholderEmail'          => __('user@example.com', 'fraud-and-scam-detection-for-woocommerce'),
                    'placeholderEmailDomain'    => __('example.com', 'fraud-and-scam-detection-for-woocommerce'),
                    'placeholderPhone'          => __('+5511999999999', 'fraud-and-scam-detection-for-woocommerce'),
                    'placeholderCountry'        => __('BR', 'fraud-and-scam-detection-for-woocommerce'),
                    'placeholderDeviceIdentity' => __('Fingerprint hash', 'fraud-and-scam-detection-for-woocommerce'),
                    'addBtn'                    => __('Ban', 'fraud-and-scam-detection-for-woocommerce'),
                    'addTitle'                  => __('Ban Item', 'fraud-and-scam-detection-for-woocommerce'),
                    'addConfirmMsg'             => __('Do you want to add the following item to the blocked list?', 'fraud-and-scam-detection-for-woocommerce'),
                    'addConfirmBtn'             => __('Confirm Ban', 'fraud-and-scam-detection-for-woocommerce'),
                    'successAdd'                => __('Item added successfully.', 'fraud-and-scam-detection-for-woocommerce'),
                    'removeBtn'                 => __('Unban', 'fraud-and-scam-detection-for-woocommerce'),
                    'removeTitle'               => __('Unban Item', 'fraud-and-scam-detection-for-woocommerce'),
                    'removeConfirmMsg'          => __('Do you want to unban the following item?', 'fraud-and-scam-detection-for-woocommerce'),
                    'removeConfirmBtn'          => __('Confirm Unban', 'fraud-and-scam-detection-for-woocommerce'),
                    'successRemove'             => __('Item removed successfully.', 'fraud-and-scam-detection-for-woocommerce'),
                    'cancel'                    => __('Cancel', 'fraud-and-scam-detection-for-woocommerce'),
                    'colValue'                  => __('Value', 'fraud-and-scam-detection-for-woocommerce'),
                    'colBannedBy'               => __('Banned By', 'fraud-and-scam-detection-for-woocommerce'),
                    'colBannedAt'               => __('Banned At', 'fraud-and-scam-detection-for-woocommerce'),
                    'colExpiresAt'              => __('Expires At', 'fraud-and-scam-detection-for-woocommerce'),
                    'forever'                   => __('Forever', 'fraud-and-scam-detection-for-woocommerce'),
                    'colActions'                => __('Actions', 'fraud-and-scam-detection-for-woocommerce'),
                    'loading'                   => __('Loading…', 'fraud-and-scam-detection-for-woocommerce'),
                    'empty'                     => __('No items.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorLoad'                 => __('Failed to load list.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorAdd'                  => __('Error adding item.', 'fraud-and-scam-detection-for-woocommerce'),
                    'errorRemove'               => __('Error removing item.', 'fraud-and-scam-detection-for-woocommerce'),
                ),
            )
        );

        wc_get_template(
            'LknFsdwFraudAndScamDetectionForWoocommerceAdminSettingsLayout.php',
            array(
                'form_fields'  => $this->get_settings(),
                'method_title' => $this->method_title,
                'gateway'      => $this,
                'install_nonce' => wp_create_nonce('install-plugin_invoice-payment-for-woocommerce'),
                'plugin_slug' => 'invoice-payment-for-woocommerce',
                'invoice_plugin_installed' => $invoice_plugin_installed,
            ),
            '',
            $template_path
        );
    }
}
