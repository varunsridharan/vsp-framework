<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Framework' ) ) {
	/**
	 * Class VSP_Framework
	 * This class should be extened and used in a plugins class
	 */
	abstract class VSP_Framework extends VSP_Framework_Admin implements VSP_Framework_Interface {
		use VSP_Framework_Trait;

		/**
		 * Settings
		 *
		 * @var null
		 */
		private $settings = null;

		/**
		 * Addons
		 *
		 * @var null
		 */
		private $addons = null;

		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'settings_page' => true,
			'reviewme'      => false,
			'addons'        => true,
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
			add_action( 'vsp_framework_init', array( $this, '__init_plugin' ) );
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
			$this->review_me();
			$this->__addon_init();

			if ( vsp_is_admin() ) {
				$this->__settings_init();
			}
			$this->init_class();
		}

		/**
		 * Adds Review Reminder Option
		 */
		protected function review_me() {
			if ( $this->option( 'reviewme' ) !== false ) {
				if ( vsp_is_admin() ) {
					vsp_load_lib( 'wpreview' );
					$this->_instance( 'VS_WP_Review_Me', false, true, $this->option( 'reviewme' ) );
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

			if ( vsp_is_admin() ) {
				$this->__register_admin_hooks();
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
				$this->addons = $this->_instance( 'VSP_Addons', false, true, $this->option( 'addons' ) );
				$this->addon_init();
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
		private function __settings_init() {
			if ( false !== $this->option( 'settings_page' ) ) {
				$this->settings_init_before();
				$this->action( 'settings_init_before' );
				$this->settings = $this->_instance( 'VSP_Settings_WPSF', false, true, $this->option( 'settings_page' ) );
				$this->settings_init();
				$this->action( 'settings_init' );
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
	}
}
