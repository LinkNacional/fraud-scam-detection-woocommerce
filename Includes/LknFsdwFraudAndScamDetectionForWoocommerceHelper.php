<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes;

if ( ! defined( 'ABSPATH' ) ) exit;

use Exception;
use WC_Logger;

class LknFsdwFraudAndScamDetectionForWoocommerceHelper {

	public function enqueueRecaptchaScripts(){
		if ( is_admin() || ! ( is_checkout() || is_cart() ) || get_option( 'lknFraudDetectionForWoocommerceEnableRecaptcha', 'no' ) !== 'yes' ) {
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
				// phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Cloudflare Turnstile requires loading from Cloudflare servers; local hosting is not supported by the service.
				'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
				[],
				FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
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
			// phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent -- Google reCAPTCHA requires loading from Google servers; local hosting is not supported by the service.
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . $googleKey,
				[],
				FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION,
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
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableRecaptcha', 'no' ) !== 'yes' ) {
			return;
		}

		$provider = get_option( 'lknFraudDetectionForWoocommerceRecaptchaSelected', 'googleRecaptchaV3' );

		if ( $provider === 'none' ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! isset( $_POST['lknFraudNonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['lknFraudNonce'] ), 'lkn_fraud_detection_checkout_nonce' ) ) {
			throw new Exception( esc_html( __( 'Security verification failed. Please try again.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		if ( $provider === 'cloudflareTurnstile' ) {
			$token = isset( $_POST['lknCfTurnstileResponse'] ) ? sanitize_text_field( wp_unslash( $_POST['lknCfTurnstileResponse'] ) ) : null;
			$this->verifyTurnstile( $token, $order );
		} else {
			$token = isset( $_POST['grecaptchav3response'] ) ? sanitize_text_field( wp_unslash( $_POST['grecaptchav3response'] ) ) : null;
			$this->verifyRecaptcha( $token, $order );
		}
	}

	public function ajax_get_orders_by_ip() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		check_ajax_referer( 'lkn_fsdw_get_orders_by_ip', 'nonce' );

		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		if ( ! $ip ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$query_args = array(
			'limit'   => 100,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		);

		if (
			class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) &&
			\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
		) {
			$query_args['customer_ip_address'] = $ip;
		} else {
			$query_args['meta_key']     = '_customer_ip_address';
			$query_args['meta_value']   = $ip;
			$query_args['meta_compare'] = '=';
		}

		$orders = wc_get_orders( $query_args );

		$data = array();
		foreach ( $orders as $order ) {
			$data[] = array(
				'id'    => $order->get_id(),
				'total' => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_total() ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
				'url'   => $order->get_edit_order_url(),
			);
		}

		wp_send_json_success( array( 'orders' => $data ) );
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
		$normalized = array_map( array( $this, 'normalize_item' ), $banned_ips );
		// Auto-remove expired items and persist the clean list
		$active = array_values( array_filter( $normalized, function( $item ) {
			return ! $this->is_expired( $item );
		} ) );
		if ( count( $active ) !== count( $normalized ) ) {
			update_option( 'lknFraudDetectionForWoocommerceBannedIps', $active );
		}
		wp_send_json_success( array( 'ips' => $active ) );
	}

	public function ajax_unban_ip() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		check_ajax_referer( 'lkn_fsdw_unban_ip', 'nonce' );

		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$banned_ips = get_option( 'lknFraudDetectionForWoocommerceBannedIps', array() );
		if ( ! is_array( $banned_ips ) ) {
			$banned_ips = array();
		}
		$banned_ips = array_values( array_filter( $banned_ips, function( $item ) use ( $ip ) {
			$normalized = $this->normalize_item( $item );
			return $normalized['value'] !== $ip;
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

		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$banned_ips = get_option( 'lknFraudDetectionForWoocommerceBannedIps', array() );
		if ( ! is_array( $banned_ips ) ) {
			$banned_ips = array();
		}
		$already_banned = false;
		foreach ( $banned_ips as $raw_item ) {
			if ( $this->normalize_item( $raw_item )['value'] === $ip ) {
				$already_banned = true;
				break;
			}
		}
		if ( ! $already_banned ) {
			$duration = (int) get_option( 'lknFraudDetectionForWoocommerceBanDuration', 0 );
			$unit     = get_option( 'lknFraudDetectionForWoocommerceBanDurationUnit', 'forever' );
			$user     = wp_get_current_user();
			$banned_ips[] = array(
				'value'      => $ip,
				'banned_by'  => $user->display_name ?: $user->user_login,
				'banned_at'  => wp_date( 'Y-m-d H:i:s' ),
				'expires_at' => $this->compute_expires_at( $duration, $unit ),
			);
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
		if ( empty( $customer_ip ) ) {
			return;
		}

		foreach ( $banned_ips as $raw_item ) {
			$item = $this->normalize_item( $raw_item );
			if ( $item['value'] !== $customer_ip || $this->is_expired( $item ) ) {
				continue;
			}

			$block_order = get_option( 'lknFraudDetectionForWoocommerceAntiFraudBehavior_block_order', 'yes' ) === 'yes';
			$mark_fraud  = get_option( 'lknFraudDetectionForWoocommerceAntiFraudBehavior_mark_fraud',  'yes' ) === 'yes';
			$add_note    = get_option( 'lknFraudDetectionForWoocommerceAntiFraudBehavior_add_note',    'yes' ) === 'yes';

			if ( $mark_fraud ) {
				$order->set_status( 'lkn-fraud' );
			}
			if ( $add_note ) {
				$order->add_order_note(
					sprintf(
						/* translators: %s: customer IP address */
						__( 'Order flagged as fraud: customer IP address %s is banned.', 'fraud-and-scam-detection-for-woocommerce' ),
						esc_html( $customer_ip )
					)
				);
			}
			if ( $mark_fraud || $add_note ) {
				$order->save();
			}
			if ( $block_order ) {
				throw new Exception( esc_html( __( 'Your IP address has been blocked from making purchases.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
			}
			break;
		}
	}

	/**
	 * Check order data against blocked data lists (email, domain, phone, country, device identity).
	 *
	 * @param \WC_Order $order
	 * @throws Exception
	 */
	public function checkBlockedData( $order ) {
		$billing_email   = strtolower( trim( $order->get_billing_email() ) );
		$billing_phone   = preg_replace( '/[^0-9+]/', '', $order->get_billing_phone() );
		$billing_country = strtoupper( trim( $order->get_billing_country() ) );

		// Block by email address
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableDataBlock_email', 'no' ) === 'yes' ) {
			$blocked = get_option( 'lknFraudDetectionForWoocommerceBlockedEmails', array() );
			if ( is_array( $blocked ) && ! empty( $billing_email ) ) {
				foreach ( $blocked as $raw ) {
					$item = $this->normalize_item( $raw );
					if ( $this->is_expired( $item ) ) { continue; }
					if ( strtolower( $item['value'] ) === $billing_email ) {
						$this->applyDataBlockBehavior( $order, __( 'email address', 'fraud-and-scam-detection-for-woocommerce' ), $billing_email );
					}
				}
			}
		}

		// Block by email domain
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableDataBlock_email_domain', 'no' ) === 'yes' ) {
			$blocked = get_option( 'lknFraudDetectionForWoocommerceBlockedEmailDomains', array() );
			$domain  = substr( strrchr( $billing_email, '@' ), 1 );
			if ( is_array( $blocked ) && ! empty( $domain ) ) {
				foreach ( $blocked as $raw ) {
					$item = $this->normalize_item( $raw );
					if ( $this->is_expired( $item ) ) { continue; }
					if ( strtolower( $item['value'] ) === $domain ) {
						$this->applyDataBlockBehavior( $order, __( 'email domain', 'fraud-and-scam-detection-for-woocommerce' ), $domain );
					}
				}
			}
		}

		// Block by phone
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableDataBlock_phone', 'no' ) === 'yes' ) {
			$blocked = get_option( 'lknFraudDetectionForWoocommerceBlockedPhones', array() );
			if ( is_array( $blocked ) && ! empty( $billing_phone ) ) {
				foreach ( $blocked as $raw ) {
					$item  = $this->normalize_item( $raw );
					if ( $this->is_expired( $item ) ) { continue; }
					$clean = preg_replace( '/[^0-9+]/', '', $item['value'] );
					if ( $clean === $billing_phone ) {
						$this->applyDataBlockBehavior( $order, __( 'phone number', 'fraud-and-scam-detection-for-woocommerce' ), $billing_phone );
					}
				}
			}
		}

		// Block by country
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableDataBlock_country', 'no' ) === 'yes' ) {
			$blocked = get_option( 'lknFraudDetectionForWoocommerceBlockedCountries', array() );
			if ( is_array( $blocked ) && ! empty( $billing_country ) ) {
				foreach ( $blocked as $raw ) {
					$item = $this->normalize_item( $raw );
					if ( $this->is_expired( $item ) ) { continue; }
					if ( strtoupper( $item['value'] ) === $billing_country ) {
						$this->applyDataBlockBehavior( $order, __( 'country', 'fraud-and-scam-detection-for-woocommerce' ), $billing_country );
					}
				}
			}
		}

		// Block by device identity
		if ( get_option( 'lknFraudDetectionForWoocommerceEnableDataBlock_device_identity', 'no' ) === 'yes' ) {
			$device_id = $order->get_meta( '_lkn_fsdw_device_identity' );
			if ( ! empty( $device_id ) ) {
				$blocked = get_option( 'lknFraudDetectionForWoocommerceBlockedDeviceIdentities', array() );
				if ( is_array( $blocked ) ) {
					foreach ( $blocked as $raw ) {
						$item = $this->normalize_item( $raw );
						if ( $this->is_expired( $item ) ) { continue; }
						if ( $item['value'] === $device_id ) {
							$this->applyDataBlockBehavior( $order, __( 'device identity', 'fraud-and-scam-detection-for-woocommerce' ), $device_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Apply the configured block behavior (mark fraud / add note / throw exception).
	 *
	 * @param \WC_Order $order
	 * @param string    $type  Human-readable type label.
	 * @param string    $value The matched value.
	 * @throws Exception
	 */
	private function applyDataBlockBehavior( $order, $type, $value ) {
		$block_order = get_option( 'lknFraudDetectionForWoocommerceAntiFraudBehavior_block_order', 'yes' ) === 'yes';
		$mark_fraud  = get_option( 'lknFraudDetectionForWoocommerceAntiFraudBehavior_mark_fraud',  'yes' ) === 'yes';
		$add_note    = get_option( 'lknFraudDetectionForWoocommerceAntiFraudBehavior_add_note',    'yes' ) === 'yes';

		if ( $mark_fraud ) {
			$order->set_status( 'lkn-fraud' );
		}
		if ( $add_note ) {
			$order->add_order_note(
				sprintf(
					/* translators: 1: data type (e.g. "email address"), 2: blocked value */
					__( 'Order flagged as fraud: %1$s "%2$s" is blocked.', 'fraud-and-scam-detection-for-woocommerce' ),
					esc_html( $type ),
					esc_html( $value )
				)
			);
		}
		if ( $mark_fraud || $add_note ) {
			$order->save();
		}
		if ( $block_order ) {
			throw new Exception( esc_html(
				sprintf(
					/* translators: %s: data type (e.g. "email address", "phone number") */
					__( 'Your order has been blocked due to a %s restriction.', 'fraud-and-scam-detection-for-woocommerce' ),
					$type
				)
			) );
		}
	}

	/**
	 * Normalize a stored ban/block item, supporting legacy plain-string entries.
	 *
	 * @param mixed $item
	 * @return array{ value: string, banned_by: string, banned_at: string|null, expires_at: string|null }
	 */
	private function normalize_item( $item ): array {
		if ( is_string( $item ) ) {
			return array(
				'value'      => $item,
				'banned_by'  => '',
				'banned_at'  => null,
				'expires_at' => null,
			);
		}
		return array(
			'value'      => (string) ( $item['value']      ?? '' ),
			'banned_by'  => (string) ( $item['banned_by']  ?? '' ),
			'banned_at'  => $item['banned_at']  ?? null,
			'expires_at' => $item['expires_at'] ?? null,
		);
	}

	/**
	 * Normalize a blocked-data value according to its type.
	 * For phones, strips everything except digits and a leading plus sign.
	 *
	 * @param string $type  e.g. 'phone', 'email', 'email_domain', 'country', 'device_identity'
	 * @param string $value Raw value.
	 * @return string Normalized value.
	 */
	private function normalize_value_by_type( string $type, string $value ): string {
		if ( 'phone' === $type ) {
			return preg_replace( '/[^0-9+]/', '', $value );
		}
		return $value;
	}

	/**
	 * Compute an expiry datetime string from a duration + unit.
	 * Returns null for forever (unit = 'forever' or duration <= 0).
	 *
	 * @param int    $duration
	 * @param string $unit  hours|days|weeks|months|years|forever
	 * @return string|null  MySQL datetime or null
	 */
	private function compute_expires_at( int $duration, string $unit ): ?string {
		if ( 'forever' === $unit || $duration <= 0 ) {
			return null;
		}
		$unit_map = array(
			'hours'  => 'hour',
			'days'   => 'day',
			'weeks'  => 'week',
			'months' => 'month',
			'years'  => 'year',
		);
		$interval_unit = $unit_map[ $unit ] ?? 'day';
		return ( new \DateTime( 'now', wp_timezone() ) )->modify( "+{$duration} {$interval_unit}" )->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Check whether a ban item has expired.
	 *
	 * @param array $item Normalized ban item.
	 * @return bool True if expired, false if still active (or forever).
	 */
	private function is_expired( array $item ): bool {
		if ( empty( $item['expires_at'] ) ) {
			return false;
		}
		return new \DateTime( 'now', wp_timezone() ) > new \DateTime( $item['expires_at'], wp_timezone() );
	}

	/**
	 * Return the wp_option name for a given blocked-data type.
	 *
	 * @param string $type
	 * @return string|null
	 */
	private function get_blocked_data_option( $type ) {
		$map = array(
			'email'           => 'lknFraudDetectionForWoocommerceBlockedEmails',
			'email_domain'    => 'lknFraudDetectionForWoocommerceBlockedEmailDomains',
			'phone'           => 'lknFraudDetectionForWoocommerceBlockedPhones',
			'country'         => 'lknFraudDetectionForWoocommerceBlockedCountries',
			'device_identity' => 'lknFraudDetectionForWoocommerceBlockedDeviceIdentities',
		);
		return $map[ $type ] ?? null;
	}

	/** AJAX: get all blocked items of a given type. */
	public function ajax_get_blocked_data() {
		check_ajax_referer( 'lkn_fsdw_get_blocked_data', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		$type   = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		$option = $this->get_blocked_data_option( $type );
		if ( ! $option ) {
			wp_send_json_error( array( 'message' => __( 'Invalid type.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		$items = get_option( $option, array() );
		if ( ! is_array( $items ) ) {
			$items = array();
		}
		$normalized = array_map( array( $this, 'normalize_item' ), $items );
		// Auto-remove expired items and persist the clean list
		$active = array_values( array_filter( $normalized, function( $item ) {
			return ! $this->is_expired( $item );
		} ) );
		if ( count( $active ) !== count( $normalized ) ) {
			update_option( $option, $active );
		}
		wp_send_json_success( array( 'items' => $active ) );
	}

	/** AJAX: add an item to a blocked-data list. */
	public function ajax_add_blocked_data() {
		check_ajax_referer( 'lkn_fsdw_add_blocked_data', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		$type  = isset( $_POST['type'] )  ? sanitize_key( wp_unslash( $_POST['type'] ) )                             : '';
		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) )                     : '';
		$value = $this->normalize_value_by_type( $type, $value );
		$option = $this->get_blocked_data_option( $type );

		if ( ! $option || empty( $value ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$items = get_option( $option, array() );
		if ( ! is_array( $items ) ) {
			$items = array();
		}
		$already_exists = false;
		foreach ( $items as $raw ) {
			$stored = $this->normalize_value_by_type( $type, $this->normalize_item( $raw )['value'] );
			if ( $stored === $value ) {
				$already_exists = true;
				break;
			}
		}
		if ( $already_exists ) {
			wp_send_json_error( array( 'message' => __( 'Item already exists.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
		$duration = (int) get_option( 'lknFraudDetectionForWoocommerceBanDuration', 0 );
		$unit     = get_option( 'lknFraudDetectionForWoocommerceBanDurationUnit', 'forever' );
		$user     = wp_get_current_user();
		$items[]  = array(
			'value'      => $value,
			'banned_by'  => $user->display_name ?: $user->user_login,
			'banned_at'  => wp_date( 'Y-m-d H:i:s' ),
			'expires_at' => $this->compute_expires_at( $duration, $unit ),
		);
		update_option( $option, $items );
		wp_send_json_success();
	}

	/** AJAX: remove an item from a blocked-data list. */
	public function ajax_remove_blocked_data() {
		check_ajax_referer( 'lkn_fsdw_remove_blocked_data', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error();
		}
		$type  = isset( $_POST['type'] )  ? sanitize_key( wp_unslash( $_POST['type'] ) )             : '';
		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) )     : '';
		$value = $this->normalize_value_by_type( $type, $value );
		$option = $this->get_blocked_data_option( $type );

		if ( ! $option || empty( $value ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}

		$items = get_option( $option, array() );
		if ( ! is_array( $items ) ) {
			wp_send_json_error();
		}
		$items = array_values( array_filter( $items, function( $i ) use ( $type, $value ) {
			$stored = $this->normalize_value_by_type( $type, $this->normalize_item( $i )['value'] );
			return $stored !== $value;
		} ) );
		update_option( $option, $items );
		wp_send_json_success();
	}

	public function verifyRecaptcha( $recaptchaResponse, $order ) {


		$score = (float) get_option('lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score');
		
		// Sanitizar o IP do cliente
		$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		
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
			throw new Exception( esc_html( 'Erro na verificação do reCAPTCHA: ' . $error_message ) );
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
			throw new Exception( esc_html( __( 'Invalid recaptcha: recaptcha was not validated.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
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
			throw new Exception( esc_html( __( 'Invalid recaptcha: score below the limit.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
		}
	}

	public function verifyTurnstile( $token, $order ) {
		$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

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
			throw new Exception( esc_html( 'Erro na verificação do Turnstile: ' . $response->get_error_message() ) );
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
			throw new Exception( esc_html( __( 'Invalid Turnstile: verification failed.', 'fraud-and-scam-detection-for-woocommerce' ) ) );
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
		   // translators: %s: number of orders with fraud status.
		   'label_count'               => _n_noop('Fraud (%s)', 'Fraud (%s)', 'fraud-and-scam-detection-for-woocommerce')
		);
		return $order_statuses;
	}

	function registerFraudStatus( $order_statuses ) {
		$order_statuses['wc-lkn-fraud'] = __('Fraud', 'fraud-and-scam-detection-for-woocommerce');
		return $order_statuses;
	}
}
