<?php
/**
 * Tests for the anti-fraud verification-failure order notes.
 *
 * Simulates the error-codes returned by the Google reCAPTCHA and Cloudflare
 * Turnstile siteverify endpoints and checks the note the plugin writes to the
 * order, so the "why was this blocked?" reason is verifiable.
 *
 * @package LknFraudDetectionForWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerceHelper;

/**
 * @covers \Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerceHelper::buildVerificationFailureNote
 */
class CaptchaVerificationFailureTest extends WP_UnitTestCase {

	/**
	 * The note always identifies the provider and says it was flagged as fraud.
	 *
	 * @dataProvider providerScenarios
	 *
	 * @param string $provider       Provider key passed to the plugin.
	 * @param string $provider_label Human-readable provider name expected in the note.
	 */
	public function test_note_names_the_provider( string $provider, string $provider_label ): void {
		$note = LknFsdwFraudAndScamDetectionForWoocommerceHelper::buildVerificationFailureNote( $provider, [ 'timeout-or-duplicate' ] );

		$this->assertStringContainsString( 'Order flagged as fraud:', $note );
		$this->assertStringContainsString( $provider_label . ' verification failed.', $note );
	}

	/**
	 * Every known error code yields its readable reason plus the raw code.
	 *
	 * @dataProvider codeScenarios
	 *
	 * @param string $code            Error code returned by the provider.
	 * @param string $expected_reason Fragment expected in the human-readable reason.
	 */
	public function test_note_explains_each_error_code( string $code, string $expected_reason ): void {
		$note = LknFsdwFraudAndScamDetectionForWoocommerceHelper::buildVerificationFailureNote( 'cloudflareTurnstile', [ $code ] );

		$this->assertStringContainsString( $expected_reason, $note );
		$this->assertStringContainsString( 'Error code: ' . $code . '.', $note );
	}

	/**
	 * When the provider returns no reason, the note still says something useful
	 * and does not emit an empty "Error code:" clause.
	 */
	public function test_note_handles_missing_error_codes(): void {
		$note = LknFsdwFraudAndScamDetectionForWoocommerceHelper::buildVerificationFailureNote( 'googleRecaptchaV3', [] );

		$this->assertStringContainsString( 'The anti-fraud provider did not return a reason for the failure.', $note );
		$this->assertStringNotContainsString( 'Error code:', $note );
	}

	/**
	 * Provider scenarios.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function providerScenarios(): array {
		return [
			'Cloudflare Turnstile' => [ 'cloudflareTurnstile', 'Cloudflare Turnstile' ],
			'Google reCAPTCHA'     => [ 'googleRecaptchaV3', 'Google reCAPTCHA' ],
		];
	}

	/**
	 * One entry per documented error code (plus an unknown one).
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function codeScenarios(): array {
		return [
			'missing-input-secret'   => [ 'missing-input-secret', 'secret key is missing' ],
			'invalid-input-secret'   => [ 'invalid-input-secret', 'secret key is invalid' ],
			'missing-input-response' => [ 'missing-input-response', 'No verification token was sent' ],
			'invalid-input-response' => [ 'invalid-input-response', 'token is invalid or malformed' ],
			'timeout-or-duplicate'   => [ 'timeout-or-duplicate', 'expired or was already used' ],
			'bad-request'            => [ 'bad-request', 'request was rejected because it was malformed' ],
			'internal-error'         => [ 'internal-error', 'internal error during verification' ],
			'unrecognized-code'      => [ 'some-unknown-code', 'unrecognized error code: some-unknown-code' ],
		];
	}
}
