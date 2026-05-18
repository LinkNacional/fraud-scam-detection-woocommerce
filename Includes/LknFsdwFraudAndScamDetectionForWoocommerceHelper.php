<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes;

use Exception;
use WC_Logger;

class LknFsdwFraudAndScamDetectionForWoocommerceHelper {

	public function enqueueRecaptchaScripts(){
		if ( ! ( is_checkout() || is_cart() ) || get_option( 'lknFraudDetectionForWoocommerceEnableRecaptcha', 'no' ) !== 'yes' ) {
			return;
		}

		$provider = get_option( 'lknFraudDetectionForWoocommerceRecaptchaSelected', 'googleRecaptchaV3' );

		if ( $provider === 'none' ) {
			return;
		}

		if ( $provider === 'cloudflareTurnstile' ) {
			$cf_site_key = get_option( 'lknFraudDetectionForWoocommerceCloudflareTurnstileSiteKey' );
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
				[],
				null,
				true
			);
			if ( is_checkout() ) {
				$terms_text = sprintf(
					'<p>%s <a href="https://www.cloudflare.com/privacypolicy/" target="_blank">%s</a> %s <a href="https://www.cloudflare.com/website-terms/" target="_blank">%s</a> %s</p>',
					__( 'This site is protected by Cloudflare Turnstile and the', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'Privacy Policy', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'and Cloudflare', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'Terms of Service', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'apply.', 'fraud-and-scam-detection-for-woocommerce' )
				);
				wp_enqueue_script(
					'lknFraudDetectionForWoocommerceTurnstile',
					FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL . 'Public/js/lknFraudDetectionForWoocommerceTurnstile.js',
					array( 'jquery', 'cloudflare-turnstile', 'wp-api-fetch' ),
					FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
					true
				);
				$cf_theme = get_option( 'lknFraudDetectionForWoocommerceCloudflareTurnstileTheme', 'light' );
				wp_localize_script( 'lknFraudDetectionForWoocommerceTurnstile', 'lknFsdwFraudScamDetectionVars', array(
					'provider'  => 'cloudflareTurnstile',
					'cfSiteKey' => $cf_site_key,
					'cfTheme'   => $cf_theme,
					'termsText' => $terms_text,
					'nonce'     => wp_create_nonce( 'lkn_fraud_detection_checkout_nonce' ),
				) );
			}
		} else {
			// Google reCAPTCHA V3 (default)
			$googleKey = get_option( 'lknFraudDetectionForWoocommercegoogleRecaptchaV3Key' );
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . $googleKey,
				[],
				null,
				true
			);
			if ( is_checkout() ) {
				$googleTermsText = sprintf(
					'<p>%s <a href="https://policies.google.com/privacy" target="_blank">%s</a> %s <a href="https://policies.google.com/terms" target="_blank">%s</a> %s</p>',
					__( 'This site is protected by reCAPTCHA and the', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'Privacy Policy', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'and Google', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'Terms of Service', 'fraud-and-scam-detection-for-woocommerce' ),
					__( 'apply.', 'fraud-and-scam-detection-for-woocommerce' )
				);
				wp_enqueue_script(
					'lknFraudDetectionForWoocommerceRecaptch',
					FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL . 'Public/js/lknFraudDetectionForWoocommerceRecaptch.js',
					array( 'jquery', 'google-recaptcha', 'wp-api-fetch' ),
					FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
					true
				);
				wp_localize_script( 'lknFraudDetectionForWoocommerceRecaptch', 'lknFsdwFraudScamDetectionVars', array(
					'provider'        => 'googleRecaptchaV3',
					'googleKey'       => $googleKey,
					'googleTermsText' => $googleTermsText,
					'termsText'       => $googleTermsText,
					'nonce'           => wp_create_nonce( 'lkn_fraud_detection_checkout_nonce' ),
				) );
			}
		}
	}

	public function processPayments($context, $result) {
		$this->checkBannedIp( $context->order );

		if ( get_option( 'lknFraudDetectionForWoocommerceEnableRecaptcha', 'no' ) !== 'yes' ) {
			return;
		}
		$payment_data = $context->payment_data;
		$provider     = get_option( 'lknFraudDetectionForWoocommerceRecaptchaSelected', 'googleRecaptchaV3' );

		if ( $provider === 'none' ) {
			return;
		}

		if ( $provider === 'cloudflareTurnstile' ) {
			$token = isset( $payment_data['lkncfturnstileresponse'] ) ? sanitize_text_field( $payment_data['lkncfturnstileresponse'] ) : null;
			$this->verifyTurnstile( $token, $context->order );
		} else {
			$token = isset( $payment_data['grecaptchav3response'] ) ? sanitize_text_field( $payment_data['grecaptchav3response'] ) : null;
			$this->verifyRecaptcha( $token, $context->order );
		}
	}

	public function verifyAjaxRequsets($orderId, $postedData, $order) {
		$this->checkBannedIp( $order );

		if ( get_option( 'lknFraudDetectionForWoocommerceEnableRecaptcha', 'no' ) !== 'yes' ) {
			return;
		}

		$provider = get_option( 'lknFraudDetectionForWoocommerceRecaptchaSelected', 'googleRecaptchaV3' );

		if ( $provider === 'none' ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! isset( $_POST['lknFraudNonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['lknFraudNonce'] ), 'lkn_fraud_detection_checkout_nonce' ) ) {
			throw new Exception( __( 'Security verification failed. Please try again.', 'fraud-and-scam-detection-for-woocommerce' ) );
		}

		if ( $provider === 'cloudflareTurnstile' ) {
			$token = isset( $_POST['lknCfTurnstileResponse'] ) ? sanitize_text_field( $_POST['lknCfTurnstileResponse'] ) : null;
			$this->verifyTurnstile( $token, $order );
		} else {
			$token = isset( $_POST['grecaptchav3response'] ) ? sanitize_text_field( $_POST['grecaptchav3response'] ) : null;
			$this->verifyRecaptcha( $token, $order );
		}
	}

	public function ajax_get_banned_ips() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		check_ajax_referer( 'lkn_fsdw_get_banned_ips', 'nonce' );

		$banned_ips = get_option( 'lknFraudDetectionForWoocommerceBannedIps', array() );
		if ( ! is_array( $banned_ips ) ) {
			$banned_ips = array();
		}
		wp_send_json_success( array( 'ips' => array_values( $banned_ips ) ) );
	}

	public function ajax_unban_ip() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		check_ajax_referer( 'lkn_fsdw_unban_ip', 'nonce' );

		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( $_POST['ip'] ) : '';
		if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$banned_ips = get_option( 'lknFraudDetectionForWoocommerceBannedIps', array() );
		if ( ! is_array( $banned_ips ) ) {
			$banned_ips = array();
		}
		$banned_ips = array_values( array_filter( $banned_ips, function( $item ) use ( $ip ) {
			return $item !== $ip;
		} ) );
		update_option( 'lknFraudDetectionForWoocommerceBannedIps', $banned_ips );

		wp_send_json_success( array( 'message' => sprintf(
			/* translators: %s: IP address */
			__( 'IP %s has been unbanned.', 'fraud-and-scam-detection-for-woocommerce' ),
			$ip
		) ) );
	}

	public function ajax_ban_ip() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		check_ajax_referer( 'lkn_fsdw_ban_ip', 'nonce' );

		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( $_POST['ip'] ) : '';
		if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$banned_ips = get_option( 'lknFraudDetectionForWoocommerceBannedIps', array() );
		if ( ! is_array( $banned_ips ) ) {
			$banned_ips = array();
		}
		if ( ! in_array( $ip, $banned_ips, true ) ) {
			$banned_ips[] = $ip;
			update_option( 'lknFraudDetectionForWoocommerceBannedIps', $banned_ips );
		}

		wp_send_json_success( array( 'message' => sprintf(
			/* translators: %s: IP address */
			__( 'IP %s has been banned.', 'fraud-and-scam-detection-for-woocommerce' ),
			$ip
		) ) );
	}

	public function checkBannedIp( $order ) {
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableIpCheck', 'no' ) !== 'yes' ) {
			return;
		}
		$banned_ips = get_option( 'lknFraudDetectionForWoocommerceBannedIps', array() );
		if ( ! is_array( $banned_ips ) || empty( $banned_ips ) ) {
			return;
		}
		$customer_ip = $order->get_customer_ip_address();
		if ( ! empty( $customer_ip ) && in_array( $customer_ip, $banned_ips, true ) ) {
			$order->set_status( 'lkn-fraud' );
			$order->save();
			throw new Exception( __( 'Your IP address has been blocked from making purchases.', 'fraud-and-scam-detection-for-woocommerce' ) );
		}
	}

	public function verifyRecaptcha($recaptchaResponse, $order){
		$score = (float) get_option('lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score');
		
		// Sanitizar o IP do cliente
		$remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
		
		$body = [
			'secret'   => get_option('lknFraudDetectionForWoocommerceGoogleRecaptchaV3Secret'),
			'response' => sanitize_text_field($recaptchaResponse),
			'remoteip' => $remote_ip
		];
		// Enviar a solicitação de verificação para o Google reCAPTCHA
		$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
			'body' => $body
		]);

		// Verificar se ocorreu um erro na requisição
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			throw new Exception('Erro na verificação do reCAPTCHA: ' . $error_message);
		}

		$responseBody = json_decode(wp_remote_retrieve_body($response), true);
		LknFsdwFraudAndScamDetectionForWoocommerceHelper::regLog(
			'info',
			'processPayments',
			array(
				'orderId' => $order->get_id(),
				'url' => 'https://www.google.com/recaptcha/api/siteverify',
				'body' => $body,
				'responseBody' => $responseBody
			)
		);

		if(!isset($responseBody['success']) || $responseBody['success'] !== true){
			$order->set_status('lkn-fraud');
			$order->save();
			throw new Exception(__('Invalid recaptcha: recaptcha was not validated.', 'fraud-and-scam-detection-for-woocommerce'));
		}

		// Verificar o score do reCAPTCHA
		if(isset($responseBody['score'])){
			$orderNote = __("Customer's ANTIFRAUD score:", 'fraud-and-scam-detection-for-woocommerce') . ' ' . $responseBody['score'];
			$scoreResponse = $responseBody['score'];

			if ($scoreResponse <= 0.3) {
				$orderNote =  $orderNote . ' ' . __('High likelihood of automated (bot) behavior.', 'fraud-and-scam-detection-for-woocommerce');
			} elseif ($scoreResponse > 0.3 && $scoreResponse < 0.6) {
				$orderNote =  $orderNote . ' ' . __('Intermediate behavior.', 'fraud-and-scam-detection-for-woocommerce');
			} elseif ($scoreResponse >= 0.6 && $scoreResponse <= 0.7) {
				$orderNote =  $orderNote . ' ' . __('Behavior generally human, but with some uncertainty.', 'fraud-and-scam-detection-for-woocommerce');
			} else {
				$orderNote =  $orderNote . ' ' . __('High likelihood of legitimate human behavior.', 'fraud-and-scam-detection-for-woocommerce');
			}

			$order->add_order_note($orderNote);
		}
		if ($responseBody['score'] < $score) {
			$order->set_status('lkn-fraud');
			$order->save();
			throw new Exception(__('Invalid recaptcha: score below the limit.', 'fraud-and-scam-detection-for-woocommerce'));
		}
	}

	public function verifyTurnstile( $token, $order ) {
		$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';

		LknFsdwFraudAndScamDetectionForWoocommerceHelper::regLog(
			'info',
			'verifyTurnstile',
			[
				'orderId'    => $order->get_id(),
				'token_len'  => strlen( (string) $token ),
				'token_empty'=> empty( $token ),
			]
		);

		$body = [
			'secret'   => get_option( 'lknFraudDetectionForWoocommerceCloudflareTurnstileSecretKey' ),
			'response' => sanitize_text_field( (string) $token ),
			'remoteip' => $remote_ip,
		];

		$response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
			'body' => $body,
		] );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Erro na verificação do Turnstile: ' . $response->get_error_message() );
		}

		$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

		LknFsdwFraudAndScamDetectionForWoocommerceHelper::regLog(
			'info',
			'verifyTurnstile',
			[
				'orderId'      => $order->get_id(),
				'url'          => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
				'success'      => isset( $responseBody['success'] ) ? $responseBody['success'] : null,
				'error-codes'  => isset( $responseBody['error-codes'] ) ? $responseBody['error-codes'] : [],
				'hostname'     => isset( $responseBody['hostname'] ) ? $responseBody['hostname'] : null,
			]
		);

		if ( ! isset( $responseBody['success'] ) || $responseBody['success'] !== true ) {
			$order->set_status( 'lkn-fraud' );
			$order->save();
			throw new Exception( __( 'Invalid Turnstile: verification failed.', 'fraud-and-scam-detection-for-woocommerce' ) );
		}

		// Cloudflare Turnstile não retorna score numérico — exibe PASS como equivalente ao score do Google
		$order->add_order_note(
			__( "Customer's ANTIFRAUD score:", 'fraud-and-scam-detection-for-woocommerce' )
			. ' PASS (Cloudflare Turnstile) '
			. __( 'High likelihood of legitimate human behavior.', 'fraud-and-scam-detection-for-woocommerce' )
		);
	}

	public static function regLog($level, $message, $context): void {
		if (get_option('lknFraudDetectionForWoocommerceDebug', 'no') == 'yes') {
			$logger = new WC_Logger();
			$logger->log($level, $message, $context);
		}
    }

	function createFraudStatus( $order_statuses ) {
		$order_statuses['wc-lkn-fraud'] = array(
		   'label' => __('Fraud', 'fraud-and-scam-detection-for-woocommerce'),
		   'public' => true,
		   'exclude_from_search' => false,
		   'show_in_admin_all_list' => true,
		   'show_in_admin_status_list' => true,
		   'label_count'               => _n_noop('Fraud (%s)', 'Fraud (%s)', 'fraud-and-scam-detection-for-woocommerce')
		);
		return $order_statuses;
	}

	function registerFraudStatus( $order_statuses ) {
		$order_statuses['wc-lkn-fraud'] = __('Fraud', 'fraud-and-scam-detection-for-woocommerce');
		return $order_statuses;
	}
}
