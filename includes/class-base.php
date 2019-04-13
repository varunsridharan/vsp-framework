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

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Base' ) ) {
	/**
	 * Class VSP_Class_Handler
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Base extends Core\Instance_Handler {
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
			vsp_doing_it_wrong( __FUNCTION__, __( 'Cloning instances of the class is forbidden.', 'vsp-framework' ), $this->option( 'version' ) );
		}

		/**
		 * WakeUp
		 */
		public function __wakeup() {
			vsp_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of the class is forbidden.', 'vsp-framework' ), $this->option( 'version' ) );
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
		protected function parse_args( $new = array(), $defaults = array() ) {
			if ( ! is_array( $new ) ) {
				$new = array();
			}
			return wp_parse_args( $new, $defaults );
		}

		/**
		 * Returns $this->file
		 *
		 * @return string
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
		 * @return string|bool
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
				'slug'      => $this->slug(),
				'db_slug'   => $this->slug( 'db' ),
				'hook_slug' => $this->slug( 'hook' ),
				'name'      => $this->plugin_name(),
				'file'      => $this->file(),
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
		 * @return mixed
		 * @uses \apply_filters()
		 */
		public function filter() {
			return $this->action_filter( 'apply_filters', func_get_args() );
		}

		/**
		 * Triggers do_action
		 *
		 * @return mixed
		 * @uses \do_action()
		 *
		 */
		public function action() {
			return $this->action_filter( 'do_action', func_get_args() );
		}


		/**
		 * Triggers add_filters
		 *
		 * @return mixed
		 * @uses \add_filters()
		 */
		public function add_filter() {
			return $this->action_filter( 'add_filter', func_get_args() );
		}

		/**
		 * Triggers add_action
		 *
		 * @return mixed
		 * @uses \add_action()
		 *
		 */
		public function add_action() {
			return $this->action_filter( 'add_action', func_get_args() );
		}


		/**
		 * Get the plugin url.
		 *
		 * @param string $ex_path
		 *
		 * @return string
		 * @see \plugins_url()
		 *
		 */
		public function plugin_url( $ex_path = '/' ) {
			return untrailingslashit( plugins_url( $ex_path, $this->file() ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @param string $ex_path
		 *
		 * @return string
		 * @see \plugin_dir_path()
		 *
		 */
		public function plugin_path( $ex_path = '' ) {
			$path = untrailingslashit( plugin_dir_path( $this->file() ) );
			return ( empty( $ex_path ) ) ? $path : $path . '/' . $ex_path;
		}

		/**
		 * Loads A Required File.
		 *
		 * @param string $file
		 * @param bool   $is_internal
		 */
		public function load_file( $file = '', $is_internal = true ) {
			$file = ( $is_internal ) ? $this->plugin_path( $file ) : $file;
			vsp_load_file( $file );
		}

		/**
		 * Get Ajax URL.
		 *
		 * @param array  $query_args
		 * @param string $scheme
		 *
		 * @return string
		 * @see \admin_url()
		 *
		 */
		public function ajax_url( $query_args = array(), $scheme = 'relative' ) {
			if ( is_array( $query_args ) ) {
				return add_query_arg( $query_args, admin_url( 'admin-ajax.php', $scheme ) );
			}
			return admin_url( 'admin-ajax.php?' . $query_args, $scheme );
		}
	}
}
