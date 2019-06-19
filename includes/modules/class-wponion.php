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
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'option_name' => false,
		);

		/**
		 * Final_options
		 *
		 * @var array
		 */
		private $final_options = array();

		/**
		 * Page_config
		 *
		 * @var array
		 */
		protected $page_config = array();

		/**
		 * @var \WPOnion\Modules\Settings\Settings
		 */
		protected $framework = null;

		/**
		 * VSP_Settings_WPOnion constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			parent::__construct( $options );
			if ( vsp_is_admin() || vsp_is_ajax() ) {
				add_action( 'wponion_loaded', array( &$this, 'init_settings' ) );
			}
		}

		/**
		 * Captures Settings Pages Array
		 */
		public function final_array() {
			$this->final_options = wponion_builder();
			$this->action( 'settings_options', $this->final_options );
		}

		/**
		 * Returns Settings Default Config
		 */
		public function get_settings_config() {
			$this->page_config = $this->options;

			if ( isset( $this->page_config['extra_js'] ) && is_array( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'][] = 'vsp_load_core_assets';
			} elseif ( isset( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'] = array( $this->page_config['extra_js'], 'vsp_load_core_assets' );
			} elseif ( ! isset( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'] = array( 'vsp_load_core_assets' );
			}
		}

		/**
		 * Inits \WPOnion\Modules\Settings Class
		 *
		 * @uses \WPOnion\Modules\Settings
		 */
		public function init_settings() {
			$this->get_settings_config();
			$this->final_array();
			if ( $this->final_options instanceof \WPO\Builder ) {
				$this->framework = wponion_settings( $this->page_config, $this->final_options );
			}
		}
	}
}
