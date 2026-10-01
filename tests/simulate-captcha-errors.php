<?php
/**
 * Standalone simulation of anti-fraud verification failures.
 *
 * Prints the order note the plugin writes for each Google reCAPTCHA /
 * Cloudflare Turnstile error code returned by the siteverify endpoints —
 * without needing the WordPress test suite, WooCommerce or a database.
 *
 * Usage (from the plugin root):
 *   php tests/simulate-captcha-errors.php
 *
 * @package LknFraudDetectionForWoocommerce
 */

// Dev-only harness: never run over HTTP (the plugin folder is web-accessible).
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

// ── Minimal WordPress stubs so the helper class can be loaded outside WP ──
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Translation stub: returns the source string (source strings are English).
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Escaping stub.
	 *
	 * @param string $text Text to escape.
	 * @return string
	 */
	function esc_html( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Sanitization stub.
	 *
	 * @param string $text Text to sanitize.
	 * @return string
	 */
	function sanitize_text_field( $text ) {
		return trim( preg_replace( '/[\r\n\t ]+/', ' ', (string) $text ) );
	}
}

require_once dirname( __DIR__ ) . '/Includes/LknFsdwFraudAndScamDetectionForWoocommerceHelper.php';

use Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerceHelper;

// ── Scenarios: [provider, [error codes]] ──────────────────
$scenarios = [
	[ 'cloudflareTurnstile', [ 'timeout-or-duplicate' ] ],
	[ 'cloudflareTurnstile', [ 'missing-input-response' ] ],
	[ 'cloudflareTurnstile', [ 'invalid-input-response' ] ],
	[ 'cloudflareTurnstile', [ 'invalid-input-secret' ] ],
	[ 'cloudflareTurnstile', [ 'bad-request' ] ],
	[ 'cloudflareTurnstile', [ 'internal-error' ] ],
	[ 'cloudflareTurnstile', [ 'some-unknown-code' ] ],
	[ 'cloudflareTurnstile', [] ],
	[ 'googleRecaptchaV3', [ 'timeout-or-duplicate' ] ],
	[ 'googleRecaptchaV3', [ 'missing-input-secret' ] ],
	[ 'googleRecaptchaV3', [ 'invalid-input-response' ] ],
];

echo "=== Simulação de falhas de validação (notas do pedido) ===\n\n";

foreach ( $scenarios as $scenario ) {
	list( $provider, $error_codes ) = $scenario;

	$provider_label = ( 'cloudflareTurnstile' === $provider ) ? 'Cloudflare Turnstile' : 'Google reCAPTCHA';
	$codes_label    = empty( $error_codes ) ? '(nenhum error-code)' : implode( ', ', $error_codes );

	echo "• {$provider_label}  →  error-codes: {$codes_label}\n";
	echo '  Nota: ' . LknFsdwFraudAndScamDetectionForWoocommerceHelper::buildVerificationFailureNote( $provider, $error_codes ) . "\n\n";
}

echo "Pronto. É exatamente esse texto que aparece em \"Notas do pedido\" no admin.\n";
