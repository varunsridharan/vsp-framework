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
	abstract class Framework extends Framework_Modules {

		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'settings_page' => false,
			'reviewme'      => false,
			'autoloader'    => false,
			'logging'       => false,
			'addons'        => false,
			'system_tools'  => false,
			'plugin_file'   => __FILE__,
		);

		/**
		 * Framework constructor.
		 *
		 * @param array $options
		 *
		 * @throws \Exception
		 */
		public function __construct( $options = array() ) {
			parent::__construct( $options );
			$this->__autoloader_init();
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

	}
}
