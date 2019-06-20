<?php

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
		 * Merges And sets the given args
		 *
		 * @param array $options .
		 * @param array $defaults .
		 */
		public function set_args( $options = array(), $defaults = array() ) {
			$defaults      = empty( $defaults ) ? $this->default_options : $defaults;
			$this->options = empty( $options ) ? array() : $options;
			$this->options = $this->parse_args( $this->options, $defaults );
		}

		/**
		 * VSP_Class_Handler constructor.
		 *
		 * @param array $options .
		 * @param array $defaults .
		 */
		public function __construct( $options = array(), $defaults = array() ) {
			$this->set_args( $options, $defaults );
			$this->class_init();

			if ( did_action( 'wponion_loaded' ) ) {
				$this->wponion();
			} else {
				add_action( 'wponion_loaded', array( &$this, 'wponion' ) );
			}
		}

		/**
		 * On WPOnion Loaded.
		 */
		public function wponion() {
		}

		/**
		 * Runs Once __construct is done.
		 */
		public function class_init() {
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
			$new = ( ! is_array( $new ) ) ? array() : $new;
			return wp_parse_args( $new, $defaults );
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
		 * @return \VSP\Framework|\VSP\Base
		 */
		public function plugin() {
			return ( $this instanceof \VSP\Framework ) ? $this : $this->get_instance( self::$framework_instance[ static::class ] );
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
			$args[0] = $this->plugin()
					->slug( 'hook' ) . '_' . $args[0];
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
			$file = $this->plugin()
				->file();
			return untrailingslashit( plugins_url( $ex_path, $file ) );
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
			$file = $this->plugin()
				->file();
			$path = untrailingslashit( plugin_dir_path( $file ) );
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
		 * @param array  $query
		 * @param string $scheme
		 *
		 * @return string
		 * @see \admin_url()
		 *
		 */
		public function ajax_url( $query = array(), $scheme = 'relative' ) {
			return ( is_array( $query ) ) ? add_query_arg( $query, admin_url( 'admin-ajax.php', $scheme ) ) : admin_url( 'admin-ajax.php?' . $query, $scheme );
		}
	}
}
