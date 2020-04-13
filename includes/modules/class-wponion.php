<?php

namespace VSP\Modules;

use VSP\Base;
use function wponion_builder;

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
		 * WPOnion constructor.
		 *
		 * @param array $options
		 *
		 * @uses \WPOnion\Modules\Settings\Settings
		 */
		public function __construct( $options = array() ) {
			$this->set_args( $options );
			if ( did_action( 'wponion_loaded' ) ) {
				$this->wpo_load();
			} else {
				add_action( 'wponion_loaded', array( &$this, 'wpo_load' ) );
			}
		}

		public function wpo_load() {

			$this->options['extra_js']   = ( isset( $this->options['extra_js'] ) ) ? $this->options['extra_js'] : array();
			$this->options['extra_js']   = ( ! is_array( $this->options['extra_js'] ) ) ? array( $this->options['extra_js'] ) : $this->options['extra_js'];
			$this->options['extra_js'][] = 'vsp_load_core_assets';
			$options                     = wponion_builder();
			$this->plugin()
				->action( 'settings_options', $options );
			if ( wpo_is( $options ) ) {
				wponion_settings( $this->options, $options );
			}
		}
	}
}
