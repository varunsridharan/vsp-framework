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
			/**
			 * @see https://docs.wponion.com/modules/settings
			 */
			'settings_page' => false,
			/**
			 * @see http://github.com/varunsridharan/wp-review-me
			 */
			'reviewme'      => false,
			/**
			 * @see http://github.com/varunsridharan/php-autoloader
			 */
			'autoloader'    => false,
			/**
			 * True / False
			 */
			'logging'       => false,
			/**
			 * @see https://github.com/varunsridharan/vsp-framework/blob/master/includes/modules/class-addons.php#L43-L51
			 */
			'addons'        => false,
			/**
			 * @see https://github.com/varunsridharan/vsp-framework/blob/master/includes/modules/class-system-tools.php#L38-L41
			 */
			'system_tools'  => false,
			/**
			 * @see https://github.com/varunsridharan/wp-localizer
			 */
			'localizer'     => false,
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
			$this->_autoloader_init();
			$this->_load_required_files();
			add_action( 'vsp_framework_init', array( &$this, '_init_plugin' ) );
		}

		/**
		 * Function Called When vsp_framework_init hook is fired
		 *
		 * @hook vsp_framework_init
		 * @uses \VSP_Framework::_admin_init()
		 * @uses VSP_Framework::plugin_init_before
		 * @uses VSP_Framework::plugin_init
		 */
		public function _init_plugin() {
			$this->plugin_init_before();
			$this->_init_class();
			$this->_register_hooks();
			$this->plugin_init();
		}

		/**
		 * This function will create a instance for all the framework classes.
		 * also provides hook
		 *
		 * @uses _init_plugin
		 * @uses init_class
		 */
		private function _init_class() {
			$this->localizer();
			$this->_init_system_tools();
			$this->_logging_init();
			$this->_review_me_init();
			$this->_addon_init();

			if ( vsp_is_admin() ) {
				$this->_settings_init();
			}

			$this->init_class();
		}

		/**
		 * Function used to register common plugin hooks
		 *
		 * @uses \VSP_Framework_Admin::_register_admin_hooks()
		 * @uses \VSP_Framework::register_hooks()
		 */
		private function _register_hooks() {
			add_action( 'init', array( $this, '_wp_init' ), 20 );
			add_filter( 'load_textdomain_mofile', array( $this, 'load_textdomain' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
			add_action( 'wponion_loaded', array( $this, 'wponion_loaded' ) );

			if ( vsp_is_admin() ) {
				$this->_register_admin_hooks();
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
		private function _load_required_files() {
			$this->load_files();
			$this->action( 'loaded' );
		}

		/**
		 * Function Calls When wp_inited
		 *
		 * @uses \VSP_Framework::wp_init
		 */
		public function _wp_init() {
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
