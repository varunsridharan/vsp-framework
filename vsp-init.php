<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VSP_Framework_Loader' ) ) {
	/**
	 * Class VSP_Framework_Loader
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 */
	final class VSP_Framework_Loader {
		/**
		 * Variable to store VSP_Framework_Loader Class instance
		 *
		 * @var \VSP_Framework_Loader
		 */
		public static $_instance = null;

		/**
		 * Stores the loaded vsp framework information
		 *
		 * @var array
		 */
		public static $_loaded = array();

		/**
		 * Maintaines All Plugins VSP Framework Details and provides them when required
		 *
		 * @var array
		 */
		public static $data = array();

		/**
		 * Array of callback to init all plugins after vsp_framework loaded
		 *
		 * @var array
		 */
		public static $callbacks = array();

		/**
		 * VSP_Framework_Loader constructor.
		 */
		public function __construct() {
			add_action( 'wponion/loaded', [ &$this, 'load_framework' ], 0 );
			add_action( 'vsp/loaded', [ &$this, 'load_plugins' ], -1 );
			add_action( 'wponion/sysinfo/datas', [ &$this, 'add_extra_info' ] );
		}

		/**
		 * Adds Custom Information To SysPage.
		 *
		 * @param array $_meta .
		 *
		 * @return array
		 */
		public function add_extra_info( $_meta = array() ) {
			$meta       = array();
			$vsp_loaded = $this->loaded();

			$meta['Framework Version']     = $vsp_loaded['Version'];
			$meta['Textdomain']            = $vsp_loaded['TextDomain'];
			$meta['DomainPath']            = $vsp_loaded['DomainPath'];
			$meta['Framework Plugin Path'] = $vsp_loaded['plugin_path'];
			$meta['Framework Path']        = $vsp_loaded['framework_path'];
			$meta['Framework Included']    = self::$data;
			$_meta['VSP Framework']        = $meta;
			return $_meta;
		}

		/**
		 * Loads Framework From A Plugin which has the latest version
		 */
		public function load_framework() {
			if ( empty( self::$_loaded ) ) {
				$lists  = self::get();
				$latest = max( array_keys( $lists ) );
				$info   = ( isset( $lists[ $latest ] ) ) ? $lists[ $latest ] : [];
				if ( empty( $info ) ) {
					$msg = base64_encode( wp_json_encode( self::$data ) );
					$ms  = 'Unable To Load VSP Framework. Please Contact The Author';
					$ms  = $ms . '<p style="word-break: break-all;"> <strong>' . 'ERROR ID : ' . '</strong>' . $msg . '</p>';
					wp_die( $ms );
				}
				self::$_loaded = $info;
				require_once $info['framework_path'] . 'vsp-bootstrap.php';
			}
		}

		/**
		 * Creates A Static Instances
		 *
		 * @return \VSP_Framework_Loader
		 */
		public static function instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Stores Framework Version & its details
		 *
		 * @param string $version framework version.
		 * @param array  $data other information.
		 *
		 * @return $this
		 */
		public function add( $version = '', $data = array() ) {
			self::$data[ $version ] = $data;
			return $this;
		}

		/**
		 * Registers a plugin and stores its deta to $data
		 *
		 * @param string $plugin_path Exact Plugin path.
		 * @param string $framework_path Foldername of the framework path.
		 *
		 * @return $this
		 */
		public function register_plugin( $plugin_path = '', $framework_path = '/vsp-framework/' ) {
			$plugin_path    = trailingslashit( $plugin_path );
			$framework_path = trailingslashit( $plugin_path . $framework_path );

			if ( file_exists( $framework_path . 'vsp-bootstrap.php' ) ) {
				$info                   = get_file_data( $framework_path . 'vsp-bootstrap.php', [
					'Name'       => 'Framework Name',
					'Version'    => 'Version',
					'TextDomain' => 'Text Domain',
					'DomainPath' => 'Domain Path',
				] );
				$info['plugin_path']    = trailingslashit( $plugin_path );
				$info['framework_path'] = $framework_path;
				self::add( $info['Version'], $info );
			}
			return $this;
		}

		/**
		 * Returns all registered plugins information
		 *
		 * @return array
		 */
		public function get() {
			return self::$data;
		}

		/**
		 * Returns currectly loaded framework information
		 *
		 * @return array
		 */
		public function loaded() {
			return self::$_loaded;
		}

		/**
		 * Registers a callback to trigger when vsp_framework is loaded
		 * usefull for plugins that dose not path vsp in-it
		 *
		 * @param array $callback .
		 *
		 * @return bool
		 */
		public function register_callback( $callback = array() ) {
			self::$callbacks[] = $callback;
			return true;
		}

		/**
		 * Loads all plugin that is registered with VSP Framework
		 */
		public function load_plugins() {
			if ( ! empty( self::$callbacks ) ) {
				foreach ( self::$callbacks as $callback ) {
					call_user_func_array( $callback, [] );
				}
			}
		}
	}
}

if ( ! function_exists( 'vsp_maybe_load' ) ) {
	/**
	 * Adds Passed Plugin path to the list array which later used to compare and
	 * load the framework from a plugin which has the latest version of framework
	 *
	 * @param array  $callback Custom function to callback when VSP is loaded.
	 * @param string $plugin_path Plugin Path To register With VSP.
	 * @param string $framework_path Exact path of the vsp framework in the plugin.
	 */
	function vsp_maybe_load( $callback = [], $plugin_path = '', $framework_path = 'vsp-framework' ) {
		$plugin_path = ( ! empty( $plugin_path ) ) ? $plugin_path : __DIR__ . '/';
		VSP_Framework_Loader::instance()
			->register_plugin( $plugin_path, $framework_path )
			->register_callback( $callback );
	}
}

if ( ! function_exists( 'vsp_register_plugin' ) ) {
	/**
	 * Registers Plugins To VSP Callback.
	 *
	 * @param array $callback .
	 *
	 * @return bool
	 */
	function vsp_register_plugin( $callback = [] ) {
		return VSP_Framework_Loader::instance()->register_callback( $callback );
	}
}

if ( ! function_exists( 'vsp_force_load' ) ) {
	/**
	 * This function is used to force load framework @ anytime.
	 * This should be mainly used on Plugin Activation and deactivation.
	 * Should not be used anywhere else.
	 */
	function vsp_force_load() {
		VSP_Framework_Loader::instance()->load_framework();
	}
}

if ( ! function_exists( 'vsp_force_load_vendors' ) ) {
	/**
	 * Loads Its libs.
	 *
	 * @return bool
	 */
	function vsp_force_load_vendors() {
		if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			include __DIR__ . '/vendor/autoload.php';
			return true;
		}
		return false;
	}
}
