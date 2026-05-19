<?php
namespace Lkn\FsdwFraudAndScamDetectionForWoocommerce\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknFsdwFraudAndScamDetectionForWoocommerce
 * @subpackage LknFsdwFraudAndScamDetectionForWoocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LknFsdwFraudAndScamDetectionForWoocommerce
 * @subpackage LknFsdwFraudAndScamDetectionForWoocommerce/admin
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknFsdwFraudAndScamDetectionForWoocommerceAdmin {
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LknFraudDetectionForWoocommerceLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LknFraudDetectionForWoocommerceLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lknFraudDetectionForWoocommerceAdmin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LknFraudDetectionForWoocommerceLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LknFraudDetectionForWoocommerceLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lknFraudDetectionForWoocommerceAdmin.js', array( 'jquery' ), $this->version, false );

		$screen = get_current_screen();
		$ip_lookup_enabled = get_option( 'lknFraudDetectionForWoocommerceEnableIpLookup', 'no' ) === 'yes';
		$ip_filter_enabled = get_option( 'lknFraudDetectionForWoocommerceEnableIpFilter', 'no' ) === 'yes';
		$ip_ban_enabled    = get_option( 'lknFraudDetectionForWoocommerceEnableIpBan', 'no' ) === 'yes';
		if (
			$screen &&
			in_array( $screen->id, array( 'woocommerce_page_wc-orders', 'shop_order' ), true ) &&
			isset( $_GET['action'] ) && 'edit' === $_GET['action'] && // phpcs:ignore WordPress.Security.NonceVerification
			get_option( 'lknFraudDetectionForWoocommerceEnableRecaptcha', 'no' ) === 'yes' &&
			( $ip_lookup_enabled || $ip_filter_enabled || $ip_ban_enabled )
		) {
			wp_enqueue_script(
				$this->plugin_name . '-order-ip-links',
				plugin_dir_url( __FILE__ ) . 'js/compiled/lknFraudDetectionForWoocommerceOrderIpLinks.COMPILED.js',
				array( 'jquery' ),
				$this->version,
				true
			);
			wp_localize_script(
				$this->plugin_name . '-order-ip-links',
				'lknFsdwOrderIpVars',
				array(
					'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
					'nonce'                => wp_create_nonce( 'lkn_fsdw_ban_ip' ),
					'nonceGet'             => wp_create_nonce( 'lkn_fsdw_get_banned_ips' ),
					'nonceUnban'           => wp_create_nonce( 'lkn_fsdw_unban_ip' ),
					'nonceFilterOrders'    => wp_create_nonce( 'lkn_fsdw_get_orders_by_ip' ),
					'ordersUrl'            => admin_url( 'admin.php?page=wc-orders' ),
					'enableIpLookup'       => $ip_lookup_enabled ? 'yes' : 'no',
					'enableIpFilter'       => $ip_filter_enabled ? 'yes' : 'no',
					'enableIpBan'          => $ip_ban_enabled    ? 'yes' : 'no',
					'i18n'       => array(
						'filterTitle'     => __( 'Order Filter by IP', 'fraud-and-scam-detection-for-woocommerce' ),
						'loading'         => __( 'Loading…', 'fraud-and-scam-detection-for-woocommerce' ),
						'noOrders'        => __( 'No orders found for this IP.', 'fraud-and-scam-detection-for-woocommerce' ),
						'colOrder'        => __( 'Order', 'fraud-and-scam-detection-for-woocommerce' ),
						'colValue'        => __( 'Value', 'fraud-and-scam-detection-for-woocommerce' ),
						'viewOrder'       => __( 'View Order', 'fraud-and-scam-detection-for-woocommerce' ),
						'ban'             => __( 'ban', 'fraud-and-scam-detection-for-woocommerce' ),
						'unban'           => __( 'unban', 'fraud-and-scam-detection-for-woocommerce' ),
						'unbanTitle'      => __( 'Unban IP', 'fraud-and-scam-detection-for-woocommerce' ),
						'unbanConfirm'    => __( 'Do you want to unban the following IP?', 'fraud-and-scam-detection-for-woocommerce' ),
						'unbanConfirmBtn' => __( 'Confirm Unban', 'fraud-and-scam-detection-for-woocommerce' ),
						'unbanning'       => __( '…', 'fraud-and-scam-detection-for-woocommerce' ),
						'ipLabel'         => __( 'IP:', 'fraud-and-scam-detection-for-woocommerce' ),
						'banTitle'        => __( 'Ban IP', 'fraud-and-scam-detection-for-woocommerce' ),
						'banConfirm'      => __( 'Do you want to ban the following IP from checkout?', 'fraud-and-scam-detection-for-woocommerce' ),
						'banConfirmBtn'   => __( 'Confirm Ban', 'fraud-and-scam-detection-for-woocommerce' ),
						'banning'         => __( 'Banning…', 'fraud-and-scam-detection-for-woocommerce' ),
						'cancel'          => __( 'Cancel', 'fraud-and-scam-detection-for-woocommerce' ),
					),
				)
			);
		}

	}

}
