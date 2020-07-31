<?php

namespace VSP\Core;

defined( 'ABSPATH' ) || exit;

use ReflectionClass;
use ReflectionException;
use VSP\Framework;

/**
 * Class VSP_Class_Handler
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
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
	 * Checks & Fetches Framework Key.
	 *
	 * @return array|bool|false|string
	 * @since {NEWVERSION}
	 */
	private function get_framework_key() {
		if ( $this instanceof Framework ) {
			return static::class;
		}

		if ( property_exists( $this, 'plugin_class' ) && ! empty( $this->plugin_class ) ) {
			return ( $this->plugin_class instanceof Framework ) ? get_class( $this->plugin_class ) : $this->plugin_class;
		}
		return false;
	}

	/**
	 * Generates Class Instance.
	 *
	 * @param string $class
	 * @param mixed  $arguments
	 *
	 * @return object
	 * @throws \ReflectionException
	 * @since {NEWVERSION}
	 */
	private function generate_instance( $class, $arguments ) {
		$refl = new ReflectionClass( $class );
		if ( $refl->isSubclassOf( '\VSP\Base' ) ) {
			$instance     = $refl->newInstanceWithoutConstructor();
			$plugin_class = $refl->getProperty( 'plugin_class' );
			$plugin_class->setAccessible( 'public' );
			$plugin_class->setValue( $instance, $this->get_framework_key() );
			$plugin_class->setAccessible( 'protected' );
			return $instance->__construct( ...$arguments );
		}
		return $refl->newInstanceArgs( $arguments );
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
				$ins = $this->generate_instance( $class, $arguments );
				$this->set_instance( $class, $ins );
			} catch ( ReflectionException $exception ) {
			}
		}
		return $this->get_instance( $class );
	}

	/**
	 * Creates New Instance & Stores It.
	 *
	 * @param string $class
	 * @param string $id
	 * @param mixed  ...$arguments
	 *
	 * @return bool|mixed
	 * @since {NEWVERSION}
	 */
	public function create( $class, $id = '', ...$arguments ) {
		$id = ( empty( $id ) ) ? $class : $id;
		if ( $this->get_instance( $id ) === false ) {
			try {
				$ins = $this->generate_instance( $class, $arguments );
				$this->set_instance( $id, $ins );
			} catch ( ReflectionException $exception ) {
			}
		}
		return $this->get_instance( $id );
	}
}
