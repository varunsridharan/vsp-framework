<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core
 * @copyright GPL V3 Or greater
 */

namespace VSP;

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
		 * Settings
		 *
		 * @var null
		 */
		private $settings = null;

		/**
		 * Autoloader.
		 *
		 * @var null|\Varunsridharan\PHP\Autoloader
		 */
		private $autoloader = null;

		/**
		 * Addons
		 *
		 * @var null|\VSP\Modules\Addons
		 */
		private $addons = null;

		/**
		 * Logging
		 *
		 * @var null|\VSP\Modules\Logger
		 */
		private $logging = null;

		/**
		 * Inits System Tools Class
		 *
		 * @uses \VSP\Modules\System_Tools
		 */
		protected function __init_system_tools() {
			if ( false !== $this->option( 'system_tools' ) ) {
				$this->_instance( '\VSP\Modules\System_Tools', false, true, $this->option( 'system_tools' ) );
			}
		}

		/**
		 * Adds Review Reminder Option
		 *
		 * @uses \Varunsridharan\WordPress\Review_Me
		 */
		protected function __review_me_init() {
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
		protected function __addon_init() {
			if ( false !== $this->option( 'addons' ) ) {
				$this->addon_init_before();
				$this->action( 'addons_init_before' );
				$this->addons = $this->_instance( '\VSP\Modules\Addons', false, true, $this->option( 'addons' ) );
				$this->addon_init();
				$this->action( 'addons_init' );
			}
		}

		/**
		 * Returns Active Addon Instance.
		 *
		 * @return \VSP\Modules\Addons
		 */
		public function addons() {
			return $this->addons;
		}

		/**
		 * Function used to init settings module
		 *
		 * @uses VSP_Framework::settings_init_before()
		 * @uses \VSP_Framework::settings_init()
		 * @hook settings_init_before
		 * @hook settings_init
		 */
		protected function __settings_init() {
			if ( false !== $this->option( 'settings_page' ) ) {
				$this->settings_init_before();
				$this->action( 'settings_init_before' );
				$args = $this->option( 'settings_page' );
				if ( is_array( $args ) ) {
					$args['option_name'] = ( isset( $args['option_name'] ) ) ? $args['option_name'] : $this->slug( 'db' );
					$this->settings      = $this->_instance( 'VSP\Modules\WPOnion', false, true, $this->option( 'settings_page' ) );
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
		public function __logging_init() {
			if ( false !== $this->option( 'logging' ) ) {
				$this->logging_init_before();
				$this->action( 'logging_init_before' );
				$this->logging = vsp_get_logger( $this->slug() );
				$this->logging_init();
				$this->action( 'loggin_init' );
			}
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
		 * Handles Autoloader.
		 *
		 * @uses \Varunsridharan\PHP\Autoloader
		 */
		protected function __autoloader_init() {
			if ( false !== $this->option( 'autoloader' ) ) {
				$args             = $this->parse_args( $this->option( 'autoloader' ), array(
					'namespace' => false,
					'base_path' => $this->plugin_path(),
					'options'   => array(),
					'prepend'   => false,
				) );
				$this->autoloader = new \Varunsridharan\PHP\Autoloader( $args['namespace'], $args['base_path'], $args['options'], $args['prepend'] );
			}
		}

		/**
		 * Returns An Active Autoloader Instance.
		 *
		 * @return \Varunsridharan\PHP\Autoloader|null
		 */
		public function autoloader() {
			return $this->autoloader;
		}
	}
}
