<?php
/**
 * Test Bootstrap — Fraud and Scam Detection for WooCommerce
 *
 * Regra de Ouro:
 *   O WooCommerce DEVE ser carregado ANTES do nosso plugin no hook
 *   `muplugins_loaded`, com prioridade mais baixa (0 vs 1).
 *   O caminho usa dirname(__DIR__, 2) para encontrar a pasta vizinha
 *   do WooCommerce no ecossistema Local WP.
 *
 * @package LknFraudDetectionForWoocommerce
 */

// ── Composer Autoload ────────────────────────────────────
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// ── Aponta para o wp-tests-config.php ────────────────────
define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );

// ── PHPUnit Polyfills ────────────────────────────────────
define(
    'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
    dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills'
);

// ── Carrega tests_add_filter() antes do bootstrap ───────
require_once dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/functions.php';

/*
 * ═══════════════════════════════════════════════════════════
 * REGRA DE OURO: WooCommerce primeiro, plugin depois.
 * ═══════════════════════════════════════════════════════════
 *
 * dirname(__DIR__, 2) a partir de tests/bootstrap.php:
 *   Nível 1 → raiz do plugin
 *   Nível 2 → wp-content/plugins/
 *   Resultado: wp-content/plugins/woocommerce/woocommerce.php
 */

tests_add_filter(
    'muplugins_loaded',
    function (): void {
        $wc_main_file = dirname( __DIR__, 2 ) . '/woocommerce/woocommerce.php';

        if ( file_exists( $wc_main_file ) ) {
            require $wc_main_file;
        }
    },
    0   // ← prioridade mais baixa: carrega ANTES de tudo
);

tests_add_filter(
    'muplugins_loaded',
    function (): void {
        require dirname( __DIR__ ) . '/fraud-scam-detection-woocommerce.php';
    },
    1   // ← prioridade após WooCommerce (0)
);

// ── Bootstrap do WordPress Test Suite ────────────────────
require_once dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';
