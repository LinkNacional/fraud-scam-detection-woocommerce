<?php
/**
 * Order simulation harness — runs the REAL verification against the REAL
 * Google reCAPTCHA / Cloudflare Turnstile siteverify endpoints and prints the
 * order notes the plugin writes.
 *
 * Run with Local's PHP (needs mysqli + the Local socket), e.g.:
 *
 *   PHPDIR="$HOME/.config/Local/lightning-services/php-8.4.4+2/bin/linux"
 *   LD_LIBRARY_PATH="$PHPDIR/shared-libs" "$PHPDIR/bin/php" \
 *     -d "mysqli.default_socket=$HOME/.config/Local/run/2R7ojtDtH/mysql/mysqld.sock" \
 *     -d "memory_limit=-1" tests/simulate-orders.php
 */

// Dev-only harness: never run over HTTP (the plugin folder is web-accessible).
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$wp_path = getenv( 'LKN_WP_PATH' ) ?: '/home/euzebio/Local Sites/ambiente-dev/app/public';

define( 'WP_USE_THEMES', false );
require $wp_path . '/wp-load.php';

use Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerceHelper;

// Optional cleanup: php tests/simulate-orders.php --cleanup
if ( in_array( '--cleanup', $argv ?? [], true ) ) {
	global $wpdb;
	// Delete only orders that carry BOTH the sim meta and the sim marker note.
	$meta_ids = wc_get_orders( [ 'limit' => -1, 'meta_key' => '_lkn_sim', 'meta_value' => '1', 'return' => 'ids' ] );
	$note_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT comment_post_ID FROM {$wpdb->comments} WHERE comment_type = %s AND comment_content LIKE %s",
			'order_note',
			'[LKN-SIM]%'
		)
	);
	$ids = array_intersect( (array) $meta_ids, (array) $note_ids );
	$count = 0;
	foreach ( $ids as $id ) {
		$order = wc_get_order( $id );
		if ( $order ) {
			$order->delete( true );
			$count++;
		}
	}
	echo "Removidos {$count} pedidos de simulação.\n";
	exit;
}

// Capture siteverify HTTP responses for display.
$GLOBALS['lkn_captured'] = [];
add_filter(
	'http_response',
	function ( $response, $args, $url ) {
		if ( false !== strpos( $url, 'siteverify' ) ) {
			$GLOBALS['lkn_captured'][] = [
				'url'  => $url,
				'body' => is_array( $response ) ? (string) $response['body'] : wp_remote_retrieve_body( $response ),
			];
		}
		return $response;
	},
	10,
	3
);

/**
 * Create a real order and tag it with a marker note.
 */
function lkn_make_order( string $label ) {
	$order = wc_create_order();
	$order->set_billing_email( 'sim+' . wp_generate_password( 6, false ) . '@example.com' );
	$order->set_billing_first_name( 'Sim' );
	$order->set_billing_last_name( 'Test' );
	$order->set_customer_ip_address( '191.241.65.120' );
	$order->update_meta_data( '_lkn_sim', 1 );
	$order->add_order_note( '[LKN-SIM] ' . $label );
	$order->save();
	return $order;
}

/**
 * Print the notes stored on an order.
 */
function lkn_print_notes( $order ) {
	$notes = wc_get_order_notes( [ 'order_id' => $order->get_id() ] );
	foreach ( $notes as $note ) {
		$flag = ( false !== strpos( $note->content, '[LKN-SIM]' ) ) ? 'marker' : 'NOTE  ';
		echo '      [' . $flag . '] ' . trim( wp_strip_all_tags( $note->content ) ) . "\n";
	}
}

/**
 * Run one scenario against the real API.
 *
 * @param string $label    Human label.
 * @param string $method   'turnstile' | 'recaptcha'.
 * @param array  $opts     Options to set temporarily (option_name => value).
 * @param string $token    Token passed to the verification.
 * @param array|null $inject Optional simulated siteverify body (tests a code
 *                           path the real test keys cannot produce).
 */
function lkn_run( string $label, string $method, array $opts, string $token, ?array $inject = null ) {
	$helper = new LknFsdwFraudAndScamDetectionForWoocommerceHelper();

	$saved = [];
	foreach ( $opts as $key => $value ) {
		$saved[ $key ] = get_option( $key );
		update_option( $key, $value );
	}

	$order = lkn_make_order( $label );

	$inject_filter = null;
	if ( null !== $inject ) {
		$inject_filter = function ( $preempt, $args, $url ) use ( $inject ) {
			return [
				'headers'  => [],
				'body'     => wp_json_encode( $inject ),
				'response' => [ 'code' => 200, 'message' => 'OK' ],
				'cookies'  => [],
				'filename' => null,
			];
		};
		add_filter( 'pre_http_request', $inject_filter, 10, 3 );
	}

	$GLOBALS['lkn_captured'] = [];
	$exception = null;
	try {
		if ( 'turnstile' === $method ) {
			$helper->verifyTurnstile( $token, $order );
		} else {
			$helper->verifyRecaptcha( $token, $order );
		}
	} catch ( Exception $e ) {
		$exception = $e->getMessage();
	} catch ( Throwable $e ) {
		$exception = get_class( $e ) . ': ' . $e->getMessage();
	}

	if ( null !== $inject_filter ) {
		remove_filter( 'pre_http_request', $inject_filter, 10 );
	}

	foreach ( $saved as $key => $value ) {
		update_option( $key, $value );
	}

	echo "\n● {$label}\n";
	echo "   ordem #{$order->get_id()}  status=" . $order->get_status() . "\n";
	if ( null !== $inject ) {
		echo '   resposta API: (simulada) ' . wp_json_encode( $inject ) . "\n";
	} elseif ( ! empty( $GLOBALS['lkn_captured'] ) ) {
		echo '   resposta API: ' . trim( $GLOBALS['lkn_captured'][ count( $GLOBALS['lkn_captured'] ) - 1 ]['body'] ) . "\n";
	}
	echo '   exceção: ' . ( $exception ? $exception : '(nenhuma)' ) . "\n";
	echo "   notas do pedido:\n";
	lkn_print_notes( $order );
}

// ── Config values ─────────────────────────────────────────
$cf_secret = 'lknFraudDetectionForWoocommerceCloudflareTurnstileSecretKey';
$gg_secret = 'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Secret';
$gg_score  = 'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score';

$cf_pass = '1x0000000000000000000000000000000AA'; // Cloudflare test key: always passes
$cf_fail = '2x0000000000000000000000000000000AA'; // Cloudflare test key: always fails
$gg_pass = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Google test key: always passes

echo "============================================================\n";
echo " CLOUDFLARE TURNSTILE — contra a API real\n";
echo "============================================================\n";

lkn_run( 'ACERTO: secret always-pass + token dummy', 'turnstile', [ $cf_secret => $cf_pass ], 'XXXX.DUMMY.TOKEN.XXXX' );
lkn_run( 'ERRO: secret always-fail', 'turnstile', [ $cf_secret => $cf_fail ], 'XXXX.DUMMY.TOKEN.XXXX' );
lkn_run( 'ERRO: token vazio (missing-input-response)', 'turnstile', [ $cf_secret => $cf_pass ], '' );
lkn_run( 'ERRO: secret inválido (invalid-input-secret)', 'turnstile', [ $cf_secret => '0xBAD000000000000ZZ' ], 'XXXX' );
lkn_run( 'ERRO: secret vazio (missing-input-secret)', 'turnstile', [ $cf_secret => '' ], 'XXXX' );

echo "\n============================================================\n";
echo " GOOGLE reCAPTCHA — contra a API real\n";
echo "============================================================\n";

lkn_run( 'ACERTO: secret test-key + token dummy (test key não devolve score)', 'recaptcha', [ $gg_secret => $gg_pass, $gg_score => '0.7' ], 'XXXX.DUMMY' );
lkn_run( 'ERRO: secret inválido', 'recaptcha', [ $gg_secret => '0xBAD0000000000ZZ', $gg_score => '0.7' ], 'XXXX' );
lkn_run(
	'ACERTO (resposta simulada com score 0.9 — test key não devolve score)',
	'recaptcha',
	[ $gg_secret => $gg_pass, $gg_score => '0.7' ],
	'XXXX.DUMMY',
	[ 'success' => true, 'score' => 0.9, 'challenge_ts' => '2026-10-01T16:00:00Z', 'hostname' => 'example.com' ]
);

echo "\n";
