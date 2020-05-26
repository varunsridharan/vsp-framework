<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

use VSP\Base;

if ( ! class_exists( '\VSP\Modules\WPOnion' ) ) {
	/**
	 * Class VSP_Settings_WPOnion
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 */
	class WPOnion extends Base {
		/**
		 * WPOnion constructor.
		 *
		 * @param array $options
		 */
		public function __construct( $options = array() ) {
			$this->set_args( $options );
			if ( did_action( 'wponion_loaded' ) ) {
				$this->wpo_load();
			} else {
				add_action( 'wponion_loaded', array( &$this, 'wpo_load' ) );
			}
		}

		/**
		 * Filters & Returns Fields.
		 *
		 * @return mixed
		 * @since {NEWVERSION}
		 */
		public function fields() {
			return $this->plugin()->action( 'settings_options', wponion_builder() );
		}

		/**
		 * Runs On WPOnion Load.
		 */
		public function wpo_load() {
			$assets   = wponion_cast_array( $this->option( 'assets', array() ) );
			$assets[] = 'vsp_load_core_assets';
			wponion_settings( $this->option(), array( &$this, 'fields' ) );
		}
	}
}
