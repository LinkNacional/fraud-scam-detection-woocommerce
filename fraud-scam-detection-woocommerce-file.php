<?php

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

use Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerce;
use Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerceActivator;
use Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes\LknFsdwFraudAndScamDetectionForWoocommerceDeactivator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if ( ! defined('FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION')) {
    define( 'FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION', '1.1.6' );
}

if ( ! defined('FRAUD_DETECTION_FOR_WOOCOMMERCE_FILE')) {
    define('FRAUD_DETECTION_FOR_WOOCOMMERCE_FILE', __DIR__ . '/fraud-scam-detection-woocommerce.php');
}

if ( ! defined('FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR')) {
    define('FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR', plugin_dir_path(FRAUD_DETECTION_FOR_WOOCOMMERCE_FILE));
}

if ( ! defined('FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL')) {
    define('FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR_URL', plugin_dir_url(FRAUD_DETECTION_FOR_WOOCOMMERCE_FILE));
}

if ( ! defined('FRAUD_DETECTION_FOR_WOOCOMMERCE_BASENAME')) {
    define('FRAUD_DETECTION_FOR_WOOCOMMERCE_BASENAME', plugin_basename(FRAUD_DETECTION_FOR_WOOCOMMERCE_FILE));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fraud-scam-detection-woocommerce-activator.php
 */
function activate_fraud_detection_for_woocommerce() {
	LknFsdwFraudAndScamDetectionForWoocommerceActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fraud-scam-detection-woocommerce-deactivator.php
 */
function deactivate_fraud_detection_for_woocommerce() {
	LknFsdwFraudAndScamDetectionForWoocommerceDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fraud_detection_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_fraud_detection_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fraud_detection_for_woocommerce() {

	$plugin = new LknFsdwFraudAndScamDetectionForWoocommerce();
	$plugin->run();

}
run_fraud_detection_for_woocommerce();
