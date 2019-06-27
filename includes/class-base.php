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
		 * Class Clone.
		 */
		public function __clone() {
			vsp_doing_it_wrong( __FUNCTION__, __( 'Cloning instances of the class is forbidden.', 'vsp-framework' ), $this->plugin()
				->version() );
		}

		/**
		 * Class Wakeup.
		 */
		public function __wakeup() {
			vsp_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of the class is forbidden.', 'vsp-framework' ), $this->plugin()
				->version() );
		}

		/**
		 * Merges And sets the given args
		 *
		 * @param array $options
		 * @param array $defaults
		 */
		protected function set_args( $options = array(), $defaults = array() ) {
			$defaults      = empty( $defaults ) ? $this->default_options : $defaults;
			$this->options = empty( $options ) ? array() : $options;
			$this->options = $this->parse_args( $this->options, $defaults );
		}

		/**
		 * Merges given array
		 *
		 * @param array $new
		 * @param array $defaults
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
		 * @param string $key
		 * @param bool   $default
		 *
		 * @return bool|mixed
		 */
		protected function option( $key = '', $default = false ) {
			return ( isset( $this->options[ $key ] ) ) ? $this->options[ $key ] : $default;
		}

		/**
		 * Sets given value for the option
		 *
		 * @param string $key
		 * @param mixed  $value
		 */
		protected function set_option( $key, $value ) {
			$this->options[ $key ] = $value;
		}

		/**
		 * @return \VSP\Framework
		 */
		public function plugin() {
			return ( $this instanceof Framework ) ? $this : $this->get_instance( self::$framework_instance[ static::class ] );
		}

		/**
		 * Get the plugin url.
		 *
		 * @param string $ex_path
		 *
		 * @return string
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
	}
}
