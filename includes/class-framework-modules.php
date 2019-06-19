<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core
 * @copyright GPL V3 Or greater
 */

namespace VSP;

use Varunsridharan\WordPress\Localizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( '\VSP\Framework_Modules' ) ) {
	/**
	 * Class Framework_Modules
	 *
	 * @package VSP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Framework_Modules extends Framework_Admin {
		/**
		 * Logging
		 *
		 * @var null|\VSP\Modules\Logger
		 */
		private $logging = null;

		/**
		 * Stores Autoloader
		 *
		 * @var null
		 * @access
		 */
		private $autoloader = null;

		/**
		 * Inits System Tools Class
		 *
		 * @uses \VSP\Modules\System_Tools
		 */
		protected function _init_system_tools() {
			if ( false !== $this->option( 'system_tools' ) ) {
				$this->_instance( '\VSP\Modules\System_Tools', false, true, $this->option( 'system_tools' ) );
			}
		}

		/**
		 * Adds Review Reminder Option
		 *
		 * @uses \Varunsridharan\WordPress\Review_Me
		 */
		protected function _review_me_init() {
			if ( false !== $this->option( 'reviewme' ) ) {
				if ( vsp_is_admin() ) {
					$this->_instance( '\Varunsridharan\WordPress\Review_Me', false, true, $this->option( 'reviewme' ) );
				}
			}
		}

		/**
		 * Function Used to init Addons Module
		 *
		 * @uses VSP_Framework::addons_init_before
		 * @uses VSP_Framework::addons_init
		 * @hook addons_init_before
		 * @hook addons_inti
		 */
		protected function _addon_init() {
			if ( false !== $this->option( 'addons' ) ) {
				$this->action( 'addons_init_before' );
				$this->_instance( '\VSP\Modules\Addons', false, true, $this->option( 'addons' ) );
				$this->action( 'addons_init' );
			}
		}

		/**
		 * Function used to init settings module
		 *
		 * @uses VSP_Framework::settings_init_before()
		 * @uses \VSP_Framework::settings_init()
		 * @hook settings_init_before
		 * @hook settings_init
		 */
		protected function _settings_init() {
			if ( false !== $this->option( 'settings_page' ) ) {
				$this->settings_init_before();
				$this->action( 'settings_init_before' );
				$args = $this->option( 'settings_page' );
				if ( is_array( $args ) ) {
					$args['option_name'] = ( isset( $args['option_name'] ) ) ? $args['option_name'] : $this->slug( 'db' );
					$this->_instance( 'VSP\Modules\WPOnion', false, true, $this->option( 'settings_page' ) );
					$this->settings_init();
					$this->action( 'settings_init' );
				}
			}
		}

		/**
		 * Inits Logger Instance.
		 *
		 * @uses \vsp_get_logger()
		 */
		public function _logging_init() {
			if ( false !== $this->option( 'logging' ) ) {
				$this->action( 'logging_init_before' );
				$this->logging = vsp_get_logger( $this->slug() );
				$this->action( 'loggin_init' );
			}
		}

		/**
		 * Handles Autoloader.
		 *
		 * @throws \Exception
		 * @uses \Varunsridharan\PHP\Autoloader
		 */
		protected function _autoloader_init() {
			if ( false !== $this->option( 'autoloader' ) ) {
				$args             = $this->parse_args( $this->option( 'autoloader' ), array(
					'namespace' => false,
					'base_path' => $this->plugin_path(),
					'options'   => array(),
				) );
				$this->autoloader = new \Varunsridharan\PHP\Autoloader( $args['namespace'], $args['base_path'], $args['options'] );
			}
		}

		/**
		 * Inits WP Localizer.
		 *
		 * @return bool|\Varunsridharan\WordPress\Localizer
		 */
		public function localizer() {
			if ( false !== $this->option( 'localizer' ) ) {
				return Localizer::instance( $this->option( 'localizer' ) );
			}
			return false;
		}

		/**
		 * Returns VSP Logger Instance.
		 *
		 * @return null|\VSP\Modules\Logger
		 */
		public function logger() {
			return $this->logging;
		}

		/**
		 * @return \Varunsridharan\PHP\Autoloader
		 */
		public function autoloader() {
			return $this->autoloader;
		}

	}
}
