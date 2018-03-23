<?php
if ( ! defined( "VSP_PATH" ) ) {
	exit;
}
if ( ! class_exists( "VSP_Core_Ajax" ) ) {
	/**
	 * Class VSP_Framework_Core_Ajax
	 */
	class VSP_Core_Ajax {

		private static $_instance = null;

		/**
		 * @return VSP_Framework_Core_Ajax
		 */
		public static function instance() {
			if ( null == self::$_instance ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		public function __construct() {
			add_action( 'wp_ajax_vsp-addon-action', array( $this, 'handle_request' ) );
		}

		public function handle_request() {
			if ( isset( $_REQUEST['hook_slug'] ) ) {
				do_action( $_REQUEST['hook_slug'] . 'handle_addon_request' );
			}

			wp_send_json_error();
		}
	}
}

return VSP_Core_Ajax::instance();