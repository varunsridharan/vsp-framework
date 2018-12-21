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

if ( ! class_exists( '\VSP\Framework' ) ) {
	/**
	 * Class VSP_Framework
	 * This class should be extened and used in a plugins class
	 *
	 * @package VSP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Framework extends Framework_Admin implements Core\Interfaces\Framework_Interface {
		use Core\Traits\Framework;

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
		 * @var null
		 */
		private $addons = null;

		/**
		 * Logging
		 *
		 * @var null|\VSP\Modules\Logger
		 */
		private $logging = null;

		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'settings_page' => true,
			'reviewme'      => false,
			'autoloader'    => false,
			'logging'       => false,
			'addons'        => true,
			'system_tools'  => false,
			'plugin_file'   => __FILE__,
		);

		/**
		 * VSP_Framework constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			parent::__construct( $options );
			$this->__load_required_files();
			add_action( 'vsp_framework_init', array( &$this, '__init_plugin' ) );
		}

		/**
		 * Function Called When vsp_framework_init hook is fired
		 *
		 * @hook vsp_framework_init
		 * @uses \VSP_Framework::__admin_init()
		 * @uses VSP_Framework::plugin_init_before
		 * @uses VSP_Framework::plugin_init
		 */
		public function __init_plugin() {
			$this->plugin_init_before();
			$this->__autoloader_init();
			$this->__init_class();
			$this->__register_hooks();
			$this->plugin_init();
		}

		/**
		 * This function will create a instance for all the framework classes.
		 * also provides hook
		 *
		 * @uses __init_plugin
		 * @uses init_class
		 */
		private function __init_class() {
			$this->__init_system_tools();
			$this->__logging_init();
			$this->__review_me_init();
			$this->__addon_init();

			if ( vsp_is_admin() ) {
				$this->__settings_init();
			}

			$this->init_class();
		}

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
					vsp_load_lib( 'wpreview' );
					$this->_instance( '\Varunsridharan\WordPress\Review_Me', false, true, $this->option( 'reviewme' ) );
				}
			}
		}

		/**
		 * Function used to register common plugin hooks
		 *
		 * @uses \VSP_Framework_Admin::__register_admin_hooks()
		 * @uses \VSP_Framework::register_hooks()
		 */
		private function __register_hooks() {
			add_action( 'init', array( $this, '__wp_init' ), 20 );
			add_filter( 'load_textdomain_mofile', array( $this, 'load_textdomain' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
			add_action( 'wponion_loaded', array( $this, 'wponion_loaded' ) );

			if ( vsp_is_admin() ) {
				$this->__register_admin_hooks();
				$this->admin_init();
			}
			$this->register_hooks();
		}

		/**
		 * Function Used to init Addons Module
		 *
		 * @uses VSP_Framework::addons_init_before
		 * @uses VSP_Framework::addons_init
		 * @hook addons_init_before
		 * @hook addons_inti
		 */
		private function __addon_init() {
			if ( false !== $this->option( 'addons' ) ) {
				$this->addon_init_before();
				$this->action( 'addons_init_before' );
				$this->addons = $this->_instance( '\VSP\Modules\Addons', false, true, $this->option( 'addons' ) );
				$this->addon_init();
				$this->action( 'addons_init' );
			}
		}

		/**
		 * Handles Autoloader.
		 *
		 * @uses \Varunsridharan\PHP\Autoloader
		 */
		private function __autoloader_init() {
			if ( false !== $this->option( 'autoloader' ) ) {
				$args             = $this->parse_args( $this->option( 'autoloader' ), array(
					'namespace' => false,
					'base_path' => $this->plugin_path(),
					'remaps'    => array(),
					'prepend'   => false,
				) );
				$this->autoloader = $this->_instance( '\Varunsridharan\PHP\Autoloader', false, false, $args );
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
		private function __settings_init() {
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
		 * Function used to load all framework required files
		 *
		 * @uses \VSP_Framework::load_files
		 * @hook loaded
		 */
		private function __load_required_files() {
			$this->load_files();
			$this->action( 'loaded' );
		}

		/**
		 * Function Calls When wp_inited
		 *
		 * @uses \VSP_Framework::wp_init
		 */
		public function __wp_init() {
			$this->wp_init();
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string      $key Constant name.
		 * @param string|bool $value Constant value.
		 */
		public function define( $key, $value = '' ) {
			if ( ! defined( $key ) ) {
				define( $key, $value );
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
		 * Returns An Active Autoloader Instance.
		 *
		 * @return \Varunsridharan\PHP\Autoloader|null
		 */
		public function autoloader() {
			return $this->autoloader;
		}
	}
}
