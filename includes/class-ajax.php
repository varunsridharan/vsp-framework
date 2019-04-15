<?php
/**
 * VSP Framework Core Ajax Handler.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core
 * @copyright GPL V3 Or greater
 *
 */

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Ajax' ) ) {
	/**
	 * Class VSP_Core_Ajax
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	final class Ajax extends Modules\Ajaxer {
		/**
		 * Ajax Action Prefix
		 *
		 * @example for wordpress_show_popup WordPress is the prefix
		 *
		 * @var string
		 */
		protected $action_prefix = 'vsp';

		/**
		 * Array of ajax actions
		 *
		 * @example array('ajax_action_1' => true,'ajax_action_2' => false)
		 *          if value set to true then it runs for both loggedout / logged in users
		 *          if value set to false then it runs only for the logged in user
		 *
		 * @var array
		 */
		protected $actions = array(
			'addon_action' => false,
			'download_log' => true,
		);

		/**
		 * VSP_Core_Ajax constructor.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Handles Ajax Request
		 */
		public function addon_action() {
			if ( isset( $_REQUEST['hook_slug'] ) ) {
				do_action( $_REQUEST['hook_slug'] . '_handle_addon_request' );
			}

			wp_send_json_error();
		}

		/**
		 * Handles Log Download.
		 */
		public function download_log() {
			if ( isset( $_REQUEST['handle'] ) && ! empty( $_REQUEST['handle'] ) ) {
				Modules\System_Logs::download_log( $_REQUEST['handle'] );
			} else {
				echo '<h2>' . __( 'Log File Not Found' ) . '</h2>';
			}
			wp_die();
		}
	}
}

return Ajax::instance();
