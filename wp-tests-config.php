<?php
/**
 * WordPress Test Configuration
 *
 * Configuração para o banco de testes local e caminho do WordPress core.
 *
 * @package LknFraudDetectionForWoocommerce
 */

// ── Database ──────────────────────────────────────────────
define( 'DB_NAME',     'local_tests' );
define( 'DB_USER',     'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST',     'localhost' );
define( 'DB_CHARSET',  'utf8' );
define( 'DB_COLLATE',  '' );

// ── WordPress Test Suite ─────────────────────────────────
define( 'WP_TESTS_DOMAIN',       'localhost' );
define( 'WP_TESTS_EMAIL',        'admin@example.com' );
define( 'WP_TESTS_TITLE',        'Test Blog' );
define( 'WP_TESTS_TABLE_PREFIX', 'wptests_' );

// ── WordPress Core Path ──────────────────────────────────
// Aponta para o WordPress core instalado via roots/wordpress
define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress/' );

// ── PHP Binary ───────────────────────────────────────────
define( 'WP_PHP_BINARY', 'php' );
