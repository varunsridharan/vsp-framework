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
	 * Class VSP_Core_Ajax
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
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
			add_action( 'wp_ajax_vsp-sys-info', array( &$this, 'render_sysinfo' ) );
			add_action( 'wp_ajax_nopriv_vsp-sys-info', array( &$this, 'render_sysinfo' ) );
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
				$output = vsp_ajax_url( array(
					'action'  => 'vsp-sys-info',
					'vsp-key' => $value,
				) );

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
					'success'   => vsp_js_alert_success( __( 'Remote URL Disabled', 'vsp-framework' ) ),
					'changeURL' => 'jQuery("a#vspsysinfocurl").attr("href","#"); jQuery("a#vspsysinfocurl").text("#")',
				) );
			}


		}

		public function render_sysinfo() {
			if ( ! isset( $_GET['vsp-key'] ) || empty( $_GET['vsp-key'] ) ) {
				return;
			}

			$query_value = $_GET['vsp-key'];
			$value       = vsp_get_cache( 'vsp-sysinfo-url' );

			if ( $query_value == $value ) {
				echo '<pre>';
				echo esc_html( VSP_System_Status_Report::text_output() );
				echo '</pre>';
				exit();
			}


			wp_die();
		}
	}
}

return VSP_Core_Ajax::instance();
