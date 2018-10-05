<?php
/**
 * VSP Framework Commom Functions for all class. (VSP_Class_Instance_Handler).
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

if ( ! class_exists( 'VSP_Class_Instance_Handler' ) ) {
	/**
	 * Class VSP_Class_Handler
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_Class_Instance_Handler {
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
		 * Returns Current Instance / create a new instance
		 *
		 * @return mixed
		 */
		public static function instance() {
			if ( ! isset( self::$_instances[ static::class ] ) ) {
				$args                              = func_get_args();
				$arg1                              = ( isset( $args[0] ) && ! empty( $args[0] ) ) ? $args[0] : array();
				$arg2                              = ( isset( $args[1] ) && ! empty( $args[1] ) ) ? $args[1] : array();
				self::$_instances[ static::class ] = new static( $arg1, $arg2 );
			}
			return self::$_instances[ static::class ];
		}

		/**
		 * Returns a new instance of a current class multiple time with new instance every time.
		 *
		 * @static
		 */
		public static function create() {
			$args = func_get_args();
			$id   = md5( wp_json_encode( $args ) );

			if ( ! isset( self::$_instances[ $id ] ) ) {
				$args                    = func_get_args();
				$arg1                    = ( isset( $args[0] ) && ! empty( $args[0] ) ) ? $args[0] : array();
				$arg2                    = ( isset( $args[1] ) && ! empty( $args[1] ) ) ? $args[1] : array();
				self::$_instances[ $id ] = new static( $arg1, $arg2 );
			}
			return self::$_instances[ $id ];
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
		 * @param        $key
		 * @param        $instance
		 */
		protected function set_instance( $key, $instance ) {
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
	}
}
