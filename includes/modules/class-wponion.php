<?php

namespace VSP\Modules;

use VSP\Base;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\VSP\Modules\WPOnion' ) ) {
	/**
	 * Class VSP_Settings_WPOnion
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class WPOnion extends Base {
		/**
		 * @var array
		 * @access
		 */
		protected $default_options = array( 'option_name' => false );

		/**
		 * Inits Class
		 */
		public function class_init() {
			if ( vsp_is_admin() || vsp_is_ajax() ) {
				add_action( 'wponion_loaded', array( &$this, 'init_settings' ) );
			}
		}

		/**
		 * @uses \WPOnion\Modules\Settings
		 */
		public function init_settings() {
			$this->options['extra_js']   = ( isset( $this->options['extra_js'] ) ) ? $this->options['extra_js'] : array();
			$this->options['extra_js']   = ( ! is_array( $this->options['extra_js'] ) ) ? array( $this->options['extra_js'] ) : $this->options['extra_js'];
			$this->options['extra_js'][] = 'vsp_load_core_assets';
			$options                     = wponion_builder();
			$this->action( 'settings_options', $options );
			if ( $options instanceof \WPO\Builder ) {
				wponion_settings( $this->options, $options );
			}
		}
	}
}
