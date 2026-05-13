<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes;

use Exception;
use WC_Logger;

class LknFsdwFraudAndScamDetectionForWoocommerceHelper {

	public function enqueueRecaptchaScripts(){
		if ((is_checkout() || is_cart()) && get_option('lknFraudDetectionForWoocommerceEnableRecaptcha', 'no') == 'yes') {
			$googleKey = get_option('lknFraudDetectionForWoocommercegoogleRecaptchaV3Key');
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . $googleKey,
				[],
				null,
				true
			);
			if (is_checkout()) {
				$googleTermsText = sprintf(
					'<p>%s <a href="https://policies.google.com/privacy" target="_blank">%s</a> %s <a href="https://policies.google.com/terms" target="_blank">%s</a> %s</p>',
					__('This site is protected by reCAPTCHA and the', 'fraud-and-scam-detection-for-woocommerce'),
					__('Privacy Policy', 'fraud-and-scam-detection-for-woocommerce'),
					__('and Google', 'fraud-and-scam-detection-for-woocommerce'),
					__('Terms of Service', 'fraud-and-scam-detection-for-woocommerce'),
					__('apply.', 'fraud-and-scam-detection-for-woocommerce'),
				);

				wp_enqueue_script( 'lknFraudDetectionForWoocommerceRecaptch', FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL . 'Public/js/lknFraudDetectionForWoocommerceRecaptch.js', array( 'jquery' ), FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION, false );
		
				wp_localize_script('lknFraudDetectionForWoocommerceRecaptch', 'lknFsdwFraudScamDetectionVars', array(
					'googleKey' => $googleKey,
					'googleTermsText' => $googleTermsText,
					'nonce' => wp_create_nonce('lkn_fraud_detection_checkout_nonce')
				));
			}
		}
	}

	public function processPayments($context, $result) {
		if(get_option('lknFraudDetectionForWoocommerceEnableRecaptcha', 'no') == 'yes'){
			// Usar dados do contexto em vez de manipular $_POST diretamente
			$paymentData = $context->payment_data;
			
			// Sanitizar a resposta do reCAPTCHA
			$recaptchaResponse = isset($paymentData['grecaptchav3response']) ? sanitize_text_field($paymentData['grecaptchav3response']) : null;
			$this->verifyRecaptcha($recaptchaResponse, $context->order);
		}
	}

	public function verifyAjaxRequsets($orderId, $postedData, $order) {
		if(get_option('lknFraudDetectionForWoocommerceEnableRecaptcha', 'no') == 'yes'){
			// Verificar nonce obrigatório - falha imediatamente se não estiver presente ou inválido
			if (!isset($_POST['lknFraudNonce']) || !wp_verify_nonce(sanitize_text_field($_POST['lknFraudNonce']), 'lkn_fraud_detection_checkout_nonce')) {
				throw new Exception(__('Security verification failed. Please try again.', 'fraud-and-scam-detection-for-woocommerce'));
			}

			// Sanitizar a resposta do reCAPTCHA
			$grecaptchav3response = isset($_POST['grecaptchav3response']) ? sanitize_text_field($_POST['grecaptchav3response']) : null;
			$this->verifyRecaptcha($grecaptchav3response, $order);
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
