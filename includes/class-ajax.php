<?php

namespace VSP;

use Varunsridharan\WordPress\Ajaxer;

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
	final class Ajax extends Ajaxer {
		/**
		 * Ajax Action Prefix
		 *
		 * @var string
		 */
		protected $action_prefix = 'vsp';

		/**
		 * Ajax actions
		 *
		 * @var array
		 */
		protected $actions = array(
			'addon_action' => false,
			'download_log' => true,
		);

		/**
		 * Handles Ajax Request
		 */
		public function addon_action() {
			if ( $this->has_request( 'hook_slug' ) ) {
				$this->validate_request( 'addon_action', __( 'Addon Action Not Provided', 'vsp-framework' ) );
				$this->validate_request( 'addon', __( 'Unable To Process Your Request', 'vsp-framework' ) );
				do_action( $_REQUEST['hook_slug'] . '_handle_addon_request', $this );
			}
			$this->json_error();
		}

		/**
		 * Handles Log Download.
		 */
		public function download_log() {
			if ( isset( $_REQUEST['handle'] ) && ! empty( $_REQUEST['handle'] ) ) {
				Modules\System_Logs::download_log( $_REQUEST['handle'] );
			} else {
				$this->error( __( 'Log File Not Found' ) );
			}
			wp_die();
		}
	}
}

return new Ajax();
