<?php
/**
 * VSP Framework Commom Functions for all class. (VSP_Class_Handler).
 *
 * @link    http://github.com/varunsridharan/vsp-framework/
 * @version 1.0
 * @since   1.0
 *
 * @package   vsp-framework/core/abstract
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Class_Handler' ) ) {
	/**
	 * Class VSP_Class_Handlers
	 */
	class VSP_Class_Handler {
		/**
		 * Stores all plugins instances
		 *
		 * @var array
		 */
		private static $_instances = array();

		/**
		 * Stores plugins class instances
		 *
		 * @var array
		 */
		protected $instances = array();

		/**
		 * Text_domain
		 *
		 * @var null
		 */
		public $text_domain = null;

		/**
		 * Version
		 *
		 * @var null
		 */
		public $version = null;

		/**
		 * File
		 *
		 * @var null
		 */
		public $file = null;

		/**
		 * Plugin Slug
		 *
		 * @var null
		 */
		public $slug = null;

		/**
		 * DB_slug
		 *
		 * @var null
		 */
		public $db_slug = null;

		/**
		 * Name
		 *
		 * @var null
		 */
		public $name = null;

		/**
		 * Hook_slug
		 *
		 * @var null
		 */
		public $hook_slug = null;

		/**
		 * Options
		 *
		 * @var array
		 */
		protected $options = array();

		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array();

		/**
		 * User_options
		 *
		 * @var array
		 */
		protected $user_options = array();

		/**
		 * Base_defaults
		 *
		 * @var array
		 */
		protected $base_defaults = array(
			'version'   => '',
			'file'      => '',
			'slug'      => '',
			'db_slug'   => '',
			'hook_slug' => '',
			'name'      => '',
		);

		/**
		 * Clone
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning instances of the class is forbidden.', 'vsp-framework' ), $this->option( 'version' ) );
		}

		/**
		 * WakeUp
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of the class is forbidden.', 'vsp-framework' ), $this->option( 'version' ) );
		}

		/**
		 * Returns Current Instance / create a new instance
		 *
		 * @return mixed
		 */
		public static function instance() {
			if ( ! isset( self::$_instances[ static::class ] ) ) {
				$args = func_get_args();
				$arg1 = ( isset( $args[0] ) && ! empty( $args[0] ) ) ? $args[0] : array();
				$arg2 = ( isset( $args[1] ) && ! empty( $args[1] ) ) ? $args[1] : array();

				self::$_instances[ static::class ] = new static( $arg1, $arg2 );
			}
			return self::$_instances[ static::class ];
		}

		/**
		 * Sets Core Values like (plugin_slug,db_slug,hook_slug) and more
		 *
		 * @param string $key .
		 * @param string $default .
		 */
		protected function _set_core( $key = '', $default = '' ) {
			if ( empty( $this->$key ) || is_null( $this->$key ) ) {
				$this->$key = $default;
			}
		}

		/**
		 * Merges And sets the given args
		 *
		 * @param array $options .
		 * @param array $defaults .
		 */
		public function set_args( $options = array(), $defaults = array() ) {
			$defaults = empty( $defaults ) ? $this->default_options : $defaults;
			$defaults = $this->parse_args( $defaults, $this->base_defaults );
			$options  = empty( $options ) ? $this->user_options : $options;
			$options  = $this->parse_args( $options, $defaults );
			$this->_set_core( 'version', $options['version'] );
			$this->_set_core( 'file', $options['file'] );
			$this->_set_core( 'slug', $options['slug'] );
			$this->_set_core( 'db_slug', $options['db_slug'] );
			$this->_set_core( 'hook_slug', $options['hook_slug'] );
			$this->_set_core( 'name', $options['name'] );
			$this->options = $options;
		}

		/**
		 * Gets Given key's instance
		 *
		 * @param string $key .
		 *
		 * @return bool|mixed
		 */
		protected function get_instance( $key ) {
			return ( isset( $this->instances[ $key ] ) ) ? $this->instances[ $key ] : false;
		}

		/**
		 * Returns all instance
		 *
		 * @return array
		 */
		protected function get_all_instances() {
			return $this->instances;
		}

		/**
		 * Creats a new instance for a given class
		 *
		 * @param string $key
		 * @param        $instance
		 */
		protected function set_instance( $key = '', $instance ) {
			$this->instances[ $key ] = $instance;
		}

		/**
		 * @param string $class .
		 * @param bool   $force_instance .
		 * @param bool   $with_args .
		 * @param array  $extra_option .
		 *
		 * @return object|VS_WP_Endpoint
		 */
		public function _instance( $class, $force_instance = false, $with_args = true, $extra_option = array() ) {
			if ( $this->get_instance( $class ) === false ) {
				$args = ( true === $with_args ) ? $this->get_common_args( $extra_option ) : $extra_option;

				if ( true === $force_instance && method_exists( $class, 'instance' ) ) {
					$this->set_instance( $class, $class::instance( $args ) );
				} else {
					$instances = new $class( $args );
					$this->set_instance( $class, $instances );
				}
			}
			return $this->get_instance( $class );
		}

		/**
		 * VSP_Class_Handler constructor.
		 *
		 * @param array $options .
		 * @param array $defaults .
		 */
		public function __construct( $options = array(), $defaults = array() ) {
			$this->set_args( $options, $defaults );
		}

		/**
		 * Merges given array
		 *
		 * @param array $new .
		 * @param array $defaults .
		 *
		 * @return array
		 */
		protected function parse_args( $new = array(), $defaults ) {
			if ( ! is_array( $new ) ) {
				$new = array();
			}
			return wp_parse_args( $new, $defaults );
		}

		/**
		 * Returns $this->file
		 *
		 * @return null
		 */
		public function file() {
			return $this->file;
		}

		/**
		 * Returns $this->version
		 *
		 * @return bool|mixed
		 */
		public function version() {
			return $this->version;
		}

		/**
		 * Returns with slug value for the given type
		 * Types (slug,db,hook)
		 *
		 * @param string $type .
		 *
		 * @return bool|null
		 */
		public function slug( $type = 'slug' ) {
			$return = false;
			switch ( $type ) {
				case 'slug':
					$return = $this->slug;
					break;
				case 'db':
					$return = $this->db_slug;
					break;
				case 'hook':
					$return = $this->hook_slug;
					break;
			}
			return $return;
		}

		/**
		 * Returns $this->name
		 *
		 * @return bool|mixed
		 */
		public function plugin_name() {
			return $this->name;
		}

		/**
		 * Returns value from options array
		 *
		 * @param string $key .
		 * @param bool   $default .
		 *
		 * @return bool|mixed
		 */
		protected function option( $key = '', $default = false ) {
			return ( isset( $this->options[ $key ] ) ) ? $this->options[ $key ] : $default;
		}

		/**
		 * Sets given value for the option
		 *
		 * @param string                          $key .
		 * @param string|object|array|int|integer $value .
		 */
		protected function set_option( $key, $value ) {
			$this->options[ $key ] = $value;
		}

		/**
		 * Updates Options array.
		 *
		 * @param array $array .
		 */
		protected function update_option( $array ) {
			$this->options = $array;
		}

		/**
		 * Returns all common array like (slug,db_slug,hook_slug,plugin_name)
		 *
		 * @param array $extra_options .
		 *
		 * @return array
		 */
		public function get_common_args( $extra_options = array() ) {
			return $this->parse_args( $extra_options, array(
				'slug'        => $this->slug(),
				'db_slug'     => $this->slug( 'db' ),
				'hook_slug'   => $this->slug( 'hook' ),
				'plugin_name' => $this->plugin_name(),
				'file'        => $this->file(),
			) );
		}

		/**
		 * Triggers Given function
		 *
		 * @param string $type .
		 * @param array  $args .
		 *
		 * @return mixed
		 */
		private function action_filter( $type = '', $args = array() ) {
			$args[0] = $this->slug( 'hook' ) . '_' . $args[0];
			return call_user_func_array( $type, $args );
		}

		/**
		 * Triggers apply_filters
		 *
		 * @uses \apply_filters()
		 * @return mixed
		 */
		public function filter() {
			return $this->action_filter( 'apply_filters', func_get_args() );
		}

		/**
		 * Triggers do_action
		 *
		 * @uses \do_action()
		 *
		 * @return mixed
		 */
		public function action() {
			return $this->action_filter( 'do_action', func_get_args() );
		}


		/**
		 * Get the plugin url.
		 *
		 * @see \plugins_url()
		 *
		 * @param string $ex_path
		 *
		 * @return string
		 */
		public function plugin_url( $ex_path = '/' ) {
			return untrailingslashit( plugins_url( $ex_path, $this->file() ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @see \plugin_dir_path()
		 *
		 * @param string $ex_path
		 *
		 * @return string
		 */
		public function plugin_path( $ex_path = '' ) {
			$path = untrailingslashit( plugin_dir_path( $this->file() ) );
			return ( empty( $ex_path ) ) ? $path : $path . '/' . $ex_path;
		}

		/**
		 * Get Ajax URL.
		 *
		 * @see \admin_url()
		 *
		 * @param array  $query_args
		 * @param string $scheme
		 *
		 * @return string
		 */
		public function ajax_url( $query_args = array(), $scheme = 'relative' ) {
			if ( is_array( $query_args ) ) {
				return add_query_arg( $query_args, admin_url( 'admin-ajax.php', $scheme ) );
			}
			return admin_url( 'admin-ajax.php?' . $query_args, $scheme );
		}
	}
}
