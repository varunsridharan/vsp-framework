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
	}
}

return VSP_Core_Ajax::instance();
