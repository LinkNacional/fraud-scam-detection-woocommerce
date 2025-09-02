<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknFsdwFraudAndScamDetectionForWoocommerceActivator
 * @subpackage LknFsdwFraudAndScamDetectionForWoocommerceActivator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    LknFsdwFraudAndScamDetectionForWoocommerceActivator
 * @subpackage LknFsdwFraudAndScamDetectionForWoocommerceActivator/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknFsdwFraudAndScamDetectionForWoocommerceActivatorI18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'fraud-scam-detection-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
