<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknFraudDetectionForWoocommerce
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$lkn_fsdw_options = array(
	'lknFraudDetectionForWoocommerceEnableRecaptcha',
	'lknFraudDetectionForWoocommerceEnableIpLookup',
	'lknFraudDetectionForWoocommerceEnableIpFilter',
	'lknFraudDetectionForWoocommerceEnableIpBan',
	'lknFraudDetectionForWoocommerceRecaptchaSelected',
	'lknFraudDetectionForWoocommerceDebug',
	'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Key',
	'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Secret',
	'lknFraudDetectionForWoocommerceGoogleRecaptchaV3Score',
	'lknFraudDetectionForWoocommerceCloudflareTurnstileSiteKey',
	'lknFraudDetectionForWoocommerceCloudflareTurnstileSecretKey',
	'lknFraudDetectionForWoocommerceCloudflareTurnstileTheme',
	'lknFraudDetectionForWoocommerceBannedIps',
	'lknFraudDetectionForWoocommerceEnableIpCheck',
	'lknFraudDetectionForWoocommerceIpBlockBehavior_block_order',
	'lknFraudDetectionForWoocommerceIpBlockBehavior_mark_fraud',
	'lknFraudDetectionForWoocommerceIpBlockBehavior_add_note',
	// Block by Data
	'lknFraudDetectionForWoocommerceEnableEmailBlock',
	'lknFraudDetectionForWoocommerceEnableEmailDomainBlock',
	'lknFraudDetectionForWoocommerceEnablePhoneBlock',
	'lknFraudDetectionForWoocommerceEnableCountryBlock',
	'lknFraudDetectionForWoocommerceEnableDeviceIdentityBlock',
	'lknFraudDetectionForWoocommerceBlockedEmails',
	'lknFraudDetectionForWoocommerceBlockedEmailDomains',
	'lknFraudDetectionForWoocommerceBlockedPhones',
	'lknFraudDetectionForWoocommerceBlockedCountries',
	'lknFraudDetectionForWoocommerceBlockedDeviceIdentities',
);

if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		foreach ( $lkn_fsdw_options as $option ) {
			delete_option( $option );
		}
		restore_current_blog();
	}
} else {
	foreach ( $lkn_fsdw_options as $option ) {
		delete_option( $option );
	}
}

// Remove user meta for all users.
delete_metadata( 'user', 0, 'lkn_fsdw_update_notice_dismissed', '', true );
