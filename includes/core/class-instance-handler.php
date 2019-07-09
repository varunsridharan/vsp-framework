<?php

namespace VSP\Core;

use ReflectionClass;
use ReflectionException;
use VSP\Framework;

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
		 * Stores Framework Instance Class.
		 *
		 * @var null
		 * @access
		 */
		protected static $framework_instance = array();

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
		 * @return bool|self|static|$this
		 */
		public static function instance() {
			$class = static::class;
			if ( ! isset( self::$_instances[ $class ] ) ) {
				try {
					$refl                       = new ReflectionClass( $class );
					self::$_instances[ $class ] = $refl->newInstanceArgs( func_get_args() );
				} catch ( ReflectionException $exception ) {

				}
			}
			return isset( self::$_instances[ $class ] ) ? self::$_instances[ $class ] : false;
		}

		/**
		 * Gets Given key's instance
		 *
		 * @param string $key .
		 *
		 * @return bool|mixed
		 */
		protected function get_instance( $key ) {
			if ( isset( self::$_instances[ $key ] ) ) {
				return self::$_instances[ $key ];
			}
			return ( isset( $this->instances[ $key ] ) ) ? $this->instances[ $key ] : false;
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
		 * @param       $class
		 * @param mixed ...$arguments
		 *
		 * @return object
		 */
		public function _instance( $class, ...$arguments ) {
			if ( $this->get_instance( $class ) === false ) {
				try {
					$framework_key = ( $this instanceof Framework ) ? static::class : false;
					$refl          = new ReflectionClass( $class );

					if ( false === $framework_key ) {
						$framework_key = ( isset( $this->framework_instance ) && ! empty( $this->framework_instance ) ) ? $this->framework_instance : false;
					}

					if ( false !== $framework_key && $refl->isSubclassOf( '\VSP\Base' ) ) {
						self::$framework_instance[ $refl->getName() ] = $framework_key;
					}

					$instances = $refl->newInstanceArgs( $arguments );
					$this->set_instance( $class, $instances );
				} catch ( ReflectionException $exception ) {

				}
			}
			return $this->get_instance( $class );
		}
	}
}
