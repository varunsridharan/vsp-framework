<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

/**
 * Class Framework
 * This class should be extened and used in a plugins class
 *
 * @package VSP
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Framework extends Framework_Modules {
	/**
	 * Returns Defaults Args.
	 *
	 * @return array|string[]
	 */
	protected function base_defaults() {
		return $this->parse_args( array(
			/* @see https://docs.wponion.com/modules/settings */
			'settings_page' => false,
			/* @see http://github.com/varunsridharan/php-autoloader */
			'autoloader'    => false,
			'logging'       => false,
			/* @see https://github.com/varunsridharan/vsp-framework/blob/master/includes/modules/class-addons.php#L43-L51 */
			'addons'        => false,
			/* @see https://github.com/varunsridharan/vsp-framework/blob/master/includes/modules/class-system-tools.php#L38-L41 */
			'system_tools'  => false,
			/* @see https://github.com/varunsridharan/wp-localizer */
			'localizer'     => false,
			'plugin_file'   => __FILE__,
		), parent::base_defaults() );
	}

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
		add_action( 'vsp/init', array( &$this, '_init_plugin' ) );
	}

	/**
	 * Function Called When vsp_framework_init hook is fired
	 *
	 * @hook vsp_framework_init
	 * @uses \VSP\Framework::plugin_init_before
	 * @uses \VSP\Framework::plugin_init
	 */
	public function _init_plugin() {
		$this->plugin_init_before();
		$this->_init_class();
		$this->_register_hooks();
		$this->plugin_init();
		$this->do_deprecated_action( 'init', null, '0.9', 'init' );
		$this->do_action( 'init' );
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
		$this->_addon_init();

		if ( vsp_is_admin() ) {
			$this->_settings_init();
		}

		$this->init_class();
	}

	/**
	 * Function used to register common plugin hooks
	 *
	 * @uses \VSP\Framework_Admin::_register_admin_hooks()
	 * @uses \VSP\Framework::register_hooks()
	 */
	private function _register_hooks() {
		add_action( 'init', array( $this, '_wp_init' ), 20 );
		/** @uses frontend_assets */
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );

		if ( vsp_is_admin() ) {
			$this->_register_admin_hooks();
			$this->admin_init();
		}
		$this->register_hooks();
	}

	/**
	 * Function used to load all framework required files
	 *
	 * @uses \VSP\Framework::load_files
	 * @hook loaded
	 */
	private function _load_required_files() {
		$this->load_files();
		$this->do_deprecated_action( 'loaded', null, '0.9', 'loaded' );
		$this->do_action( 'loaded' );
	}

	/**
	 * Function Calls When wp_inited
	 *
	 * @uses \VSP\Framework::wp_init
	 */
	public function _wp_init() {
		$this->wp_init();
	}

	/**
	 * @see \VSP\Framework->__register_hooks
	 */
	protected function register_hooks() {
	}

	/**
	 * @see \VSP\Framework::__load_required_files
	 */
	protected function load_files() {
	}
}
