<?php
/**
 * VSP Framework Core Ajax Handler.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 *
 */
if ( ! defined( 'VSP_PATH' ) ) {
	exit;
}
if ( ! class_exists( 'VSP_Core_Ajax' ) ) {
	/**
	 * Class VSP_Framework_Core_Ajax
	 */
	class VSP_Core_Ajax {
		/**
		 * Instance
		 *
		 * @var null
		 */
		private static $_instance = null;

		/**
		 * Creates Instance for VSP_Core_Ajax
		 *
		 * @return VSP_Core_Ajax
		 */
		public static function instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * VSP_Core_Ajax constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_vsp-addon-action', array( $this, 'handle_request' ) );
			add_action( 'wp_ajax_vsp-sysinfo-remote', array( &$this, 'handle_sysurl_generate' ) );
		}

		/**
		 * Handles Ajax Request
		 */
		public function handle_request() {
			if ( isset( $_REQUEST['hook_slug'] ) ) {
				do_action( $_REQUEST['hook_slug'] . 'handle_addon_request' );
			}

			wp_send_json_error();
		}

		public function handle_sysurl_generate() {
			if ( 'generate' === $_REQUEST['sysinfo_action'] ) {
				$value  = wp_hash( microtime( true ) . wp_generate_password( 10, true ) );
				$output = home_url() . '/?vsp-sys-info=' . $value;
				vsp_set_cache( 'vsp-sysinfo-url', $value, '2_days' );
				vsp_send_json_callback( true, array(
					'success'   => vsp_js_alert_success( __( 'URL Generated', 'vsp-framework' ), __( 'Remote View URL Generated. due to security reasons this url will only be valid for 48hrs from now.', 'vsp-framework' ), array(
						'content' => array(
							'element'    => 'input',
							'attributes' => array(
								'value' => $output,
							),
						),
					) ),
					'changeURL' => 'jQuery("a#vspsysinfocurl").attr("href","' . $output . '"); jQuery("a#vspsysinfocurl").text("' . $output . '")',
				) );
			} else {
				vsp_delete_cache( 'vsp-sysinfo-url' );
				vsp_send_json_callback( true, array(
					'success' => vsp_js_alert_success( __( 'Remote URL Disabled', 'vsp-framework' ) ),
					'changeURL' => 'jQuery("a#vspsysinfocurl").attr("href","#"); jQuery("a#vspsysinfocurl").text("#")',
				) );
			}


		}
	}
}

return VSP_Core_Ajax::instance();
