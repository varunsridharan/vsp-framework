<?php

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

use Varunsridharan\WordPress\Ajaxer as VS_Ajaxer;

if ( ! class_exists( '\VSP\Ajaxer' ) ) {
	/**
	 * Class Ajaxer
	 *
	 * @package VSP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Ajaxer extends VS_Ajaxer {
		/**
		 * @var bool
		 * @access
		 */
		protected $wpo_assets = false;

		/**
		 * Merge user defined arguments into defaults array.
		 *
		 * @param $data
		 *
		 * @return array
		 */
		private function vsp_args( $data ) {
			return wp_parse_args( wponion_ajax_args( $this->wpo_assets ), $data );
		}

		/**
		 * Send a JSON response back to an Ajax request, indicating failure.
		 *
		 * @param mixed $data
		 * @param null  $status_code
		 */
		public function json_error( $data = null, $status_code = null ) {
			wp_send_json_error( $this->vsp_args( $data ), $status_code );
		}

		/**
		 * Send a JSON response back to an Ajax request, indicating success.
		 *
		 * @param mixed $data
		 * @param null  $status_code
		 */
		public function json_success( $data = null, $status_code = null ) {
			wp_send_json_success( $this->vsp_args( $data ), $status_code );
		}
	}
}

return new Ajax();
