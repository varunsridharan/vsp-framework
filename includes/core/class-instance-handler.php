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

namespace VSP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\VSP\Core\Instance_Handler' ) ) {
	/**
	 * Class VSP_Class_Handler
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Instance_Handler {
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
		 * Returns Current Instance / create a new instance
		 *
		 * @static
		 * @return bool|$this|static
		 */
		public static function instance() {
			if ( ! isset( self::$_instances[ static::class ] ) ) {
				try {
					$refl                              = new \ReflectionClass( static::class );
					self::$_instances[ static::class ] = $refl->newInstanceArgs( func_get_args() );
				} catch ( \ReflectionException $exception ) {

				}
			}
			return isset( self::$_instances[ static::class ] ) ? self::$_instances[ static::class ] : false;
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
		 * @return object
		 */
		public function _instance( $class, $force_instance = false, $with_args = true, $extra_option = array() ) {
			if ( $this->get_instance( $class ) === false ) {
				$args = $extra_option;
				if ( true === $with_args && method_exists( $this, 'get_common_args' ) ) {
					$args = $this->get_common_args( $extra_option );
				}

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
