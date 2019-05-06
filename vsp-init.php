<?php
/**
 * VSP Framework INIT File.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

if ( ! class_exists( 'VSP_Framework_Loader' ) ) {
	/**
	 * Class VSP_Framework_Loader
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
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
		 * Array of Libs & Integrations to be loaded after vsp framework loaded
		 * array collected when plugin registers with vsp
		 *
		 * @var array
		 */
		public static $meta_data = array();

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
			self::$meta_data = [
				'lib'          => [],
				'integrations' => [],
			];
			add_action( 'plugins_loaded', [ &$this, 'load_framework' ], 0 );
			add_action( 'vsp_framework_load_lib_integrations', [ &$this, 'load_libs_integrations' ], 0 );
			add_action( 'vsp_framework_loaded', [ &$this, 'load_plugins' ] );
		}

		/**
		 * Adds Custom Information To SysPage.
		 *
		 * @param array $meta .
		 *
		 * @return array
		 */
		public function add_extra_info( $meta = array() ) {
			$integrations = \VSP\Autoloader::get_integrations();
			$libs         = \VSP\Autoloader::get_libs();
			$vsp_loaded   = $this->loaded();

			$meta[ __( 'Framework Version', 'vsp-framework' ) ]     = $vsp_loaded['Version'];
			$meta[ __( 'Textdomain', 'vsp-framework' ) ]            = $vsp_loaded['TextDomain'];
			$meta[ __( 'DomainPath', 'vsp-framework' ) ]            = $vsp_loaded['DomainPath'];
			$meta[ __( 'Framework Plugin Path', 'vsp-framework' ) ] = vsp_censor_path( $vsp_loaded['plugin_path'] );
			$meta[ __( 'Framework Path', 'vsp-framework' ) ]        = vsp_censor_path( $vsp_loaded['framework_path'] );
			$meta[ __( 'Loaded Library', 'vsp-framework' ) ]        = self::$meta_data['lib'];
			$meta[ __( 'Loaded Integration', 'vsp-framework' ) ]    = self::$meta_data['integrations'];
			$meta[ __( 'Bundled Integrations', 'vsp-framework' ) ]  = array();
			$meta[ __( 'Bundled Libs', 'vsp-framework' ) ]          = array();

			foreach ( $integrations as $k => $v ) {
				$data = get_file_data( \VSP\Autoloader::integration_path() . $v, [
					'Name'    => '@name',
					'Version' => '@version',
				], 'vsp' );

				if ( count( array_filter( $data ) ) === 2 ) {
					$meta[ __( 'Bundled Integrations', 'vsp-framework' ) ][] = $data['Name'] . ' - ' . $data['Version'] . ' - ' . $v;
				} else {
					$meta[ __( 'Bundled Integrations', 'vsp-framework' ) ][] = $k . ' - ' . $v;
				}
			}

			foreach ( $libs as $k => $v ) {
				$data = get_file_data( \VSP\Autoloader::lib_path() . $v, [
					'Name'    => 'Name',
					'Version' => 'Version',
				], 'vsp' );

				if ( count( array_filter( $data ) ) === 2 ) {
					$meta[ __( 'Bundled Libs', 'vsp-framework' ) ][] = $data['Name'] . ' - ' . $data['Version'] . ' - ' . $v;
				} else {
					$meta[ __( 'Bundled Libs', 'vsp-framework' ) ][] = $k . ' - ' . $v;
				}
			}

			return $meta;
		}

		/**
		 * Loads Framework From A Plugin which has the latest version
		 */
		public function load_framework() {
			$frameworks     = self::get();
			$latest_version = max( array_keys( $frameworks ) );
			$info           = ( isset( $frameworks[ $latest_version ] ) ) ? $frameworks[ $latest_version ] : [];
			if ( empty( $info ) ) {
				$msg = base64_encode( wp_json_encode( self::$data ) );
				$ms  = __( 'Unable To Load VSP Framework. Please Contact The Author', 'vsp-framework' );
				$ms  = $ms . '<p style="word-break: break-all;"> <strong>' . __( 'ERROR ID : ', 'vsp-framework' ) . '</strong>' . $msg . '</p>';
				wp_die( $ms );
			}
			self::$_loaded = $info;
			require_once $info['framework_path'] . 'vsp-bootstrap.php';
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
		 * Merges With $meta_data (libs & Integrations) request
		 *
		 * @param array $data Array of Libs & Integrations.
		 */
		public function manage_meta_data( $data = array() ) {
			if ( isset( $data['lib'] ) && ! empty( $data['lib'] ) ) {
				self::$meta_data['lib'] = array_merge( self::$meta_data['lib'], $data['lib'] );
			}

			if ( isset( $data['integrations'] ) && ! empty( $data['integrations'] ) ) {
				self::$meta_data['integrations'] = array_merge( self::$meta_data['integrations'], $data['integrations'] );
			}
		}

		/**
		 * Registers a plugin and stores its deta to $data
		 *
		 * @param string $plugin_path Exact Plugin path.
		 * @param array  $meta_data Information such as Lib & Integrations which need to be loaded for this plugin.
		 * @param string $framework_path Foldername of the framework path.
		 *
		 * @return $this
		 */
		public function register_plugin( $plugin_path = '', $meta_data = [], $framework_path = '/vsp-framework/' ) {
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

			$this->manage_meta_data( $meta_data );
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
		 * Loads Required Libs & Integrations files.
		 *
		 * @hook vsp_framework_load_lib_integrations
		 */
		public function load_libs_integrations() {
			if ( ! empty( self::$meta_data['lib'] ) ) {
				foreach ( self::$meta_data['lib'] as $lib ) {
					vsp_load_lib( $lib );
				}
			}

			if ( ! empty( self::$meta_data['integrations'] ) ) {
				foreach ( self::$meta_data['integrations'] as $lib ) {
					vsp_load_integration( $lib );
				}
			}
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
	 * @param string $plugin_path Plugin Path To register With VSP.
	 * @param array  $meta_data Array of data like Libs & Integrations to load.
	 * @param array  $callback Custom function to callback when VSP is loaded.
	 * @param string $framework_path Exact path of the vsp framework in the plugin.
	 */
	function vsp_maybe_load( $callback = [], $meta_data = [], $plugin_path = '', $framework_path = '' ) {
		$plugin_path = ( ! empty( $plugin_path ) ) ? $plugin_path : __DIR__ . '/';
		VSP_Framework_Loader::instance()
			->register_plugin( $plugin_path, $meta_data, $framework_path )
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
		$framework = VSP_Framework_Loader::instance();
		return $framework->register_callback( $callback );
	}
}

if ( ! function_exists( 'vsp_force_load' ) ) {
	/**
	 * This function is used to force load framework @ anytime.
	 * This should be mainly used on Plugin Activation and deactivation.
	 * Should not be used anywhere else.
	 */
	function vsp_force_load() {
		VSP_Framework_Loader::instance()
			->load_framework();
	}
}