<?php

namespace VSP;

use Varunsridharan\WordPress\Ajaxer as VS_Ajaxer;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

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
		 * @param $data
		 *
		 * @return array
		 */
		private function vsp_args( $data ) {
			return wp_parse_args( wponion_ajax_args( $this->wpo_assets ), $data );
		}

		/**
		 * @param mixed $data
		 * @param null  $status_code
		 */
		public function json_error( $data = null, $status_code = null ) {
			wp_send_json_error( $this->vsp_args( $data ), $status_code );
		}

		/**
		 * @param mixed $data
		 * @param null  $status_code
		 */
		public function json_success( $data = null, $status_code = null ) {
			wp_send_json_success( $this->vsp_args( $data ), $status_code );
		}
	}
}

return new Ajax();
