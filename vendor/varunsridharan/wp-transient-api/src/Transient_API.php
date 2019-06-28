<?php
/**
 * WordPress Transient API
 * This library provides developers to manage all their Transients with version management.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

namespace Varunsridharan\WordPress;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( '\Varunsridharan\WordPress\Transient_Api' ) ) {
	/**
	 * Class Transient_WP_Api
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Transient_Api {
		/**
		 * _instances
		 *
		 * @var array
		 */
		protected static $_instances = array();

		/**
		 * Stores All Options.
		 *
		 * @var array
		 * @access
		 */
		protected $options = array();

		/**
		 * Transient_Api constructor.
		 *
		 * @param array $options
		 */
		public function __construct( $options = array() ) {
			$this->options = wp_parse_args( $options, array(
				// Transients
				'transient_version'     => 1.0,
				'transient_auto_delete' => false,
				'transient_surfix'      => '',
				'transient_prefix'      => '',
				// WP DB Options
				'option_auto_delete'    => false,
				'option_version'        => 1.0,
				'option_surfix'         => '',
				'option_prefix'         => '',
				// Global Config.
				'is_option'             => false,
			) );
		}

		/**
		 * @param      $key
		 * @param bool $default
		 *
		 * @return bool|mixed
		 */
		protected function option( $key, $default = false ) {
			return ( isset( $this->options[ $key ] ) ) ? $this->options[ $key ] : $default;
		}

		/**
		 * Returns a Unique Key.
		 *
		 * @param string $key
		 * @param bool   $is_option
		 *
		 * @return mixed
		 */
		public function key( $key = '', $is_option = false ) {
			return $this->get_key( $this->validate_length( $key ), $is_option );
		}

		/**
		 * Returns Key Based on Requirement.
		 *
		 * @param string $key
		 * @param bool   $is_option
		 *
		 * @return string
		 */
		public function get_key( $key = '', $is_option = false ) {
			if ( true === $is_option ) {
				return $this->option( 'option_prefix', '' ) . $key . $this->option( 'option_surfix', '' );
			}

			return $this->option( 'transient_prefix', '' ) . $key . $this->option( 'transient_surfix', '' );
		}

		/**
		 * Validates Key Length if key lenght exceeds it will md5 or returns the orginal key
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		protected function validate_length( $key = '' ) {
			return ( $this->check_length( $key ) === false ) ? $this->validate_length( md5( $key ) ) : $key;
		}

		/**
		 * Returns if key is in correct length
		 * if Type set to option then it uses $this->option_limit for char limit
		 * or uses $this->transient_limit for char limit
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		protected function check_length( $key = '' ) {
			if ( true === $this->option( 'is_option' ) ) {
				return ( strlen( $this->get_key( $key ) ) > $this->option( 'option_limit' ) ) ? false : true;
			}

			return ( strlen( $this->get_key( $key ) ) > $this->option( 'transient_limit' ) ) ? false : true;
		}

		/**
		 * Adds Given value to db using add_option
		 *
		 * @param string $key
		 * @param mixed  $value
		 * @param string $autoload
		 *
		 * @return bool
		 */
		protected function wp_add_option( $key = '', $value = '', $autoload = 'no' ) {
			return \add_option( $key, $value, '', $autoload );
		}

		/**
		 * Updates Given value to db using update_option
		 *
		 * @param string $key
		 * @param mixed  $value
		 * @param string $autoload
		 *
		 * @return bool
		 */
		protected function wp_update_option( $key = '', $value = '', $autoload = 'no' ) {
			return \update_option( $key, $value, $autoload );
		}

		/**
		 * Deletes a given option
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		protected function wp_delete_option( $key = '' ) {
			return \delete_option( $key );
		}

		/**
		 * Gets An Option From DB
		 *
		 * @param string $key
		 * @param bool   $default
		 *
		 * @return mixed|void
		 */
		protected function wp_get_option( $key = '', $default = false ) {
			return \get_option( $key, $default );
		}

		/**
		 * Sets an transient using Transient API In WP
		 *
		 * @param     $transient
		 * @param     $value
		 * @param int $expiration
		 *
		 * @return bool
		 */
		protected function wp_set_transient( $transient, $value, $expiration = 0 ) {
			return \set_transient( $transient, $value, $expiration );
		}

		/**
		 * Gets an transient using Transient API In WP
		 *
		 * @param $transient
		 *
		 * @return mixed
		 */
		protected function wp_get_transient( $transient ) {
			return \get_transient( $transient );
		}

		/**
		 * Deletes an transient using Transient API In WP
		 *
		 * @param $transient
		 *
		 * @return bool
		 */
		protected function wp_delete_transient( $transient ) {
			return \delete_transient( $transient );
		}

		/**
		 * Returns Version Key.
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		protected function get_version_key( $key = '' ) {
			return $this->validate_length( $key . '-version' );
		}

		/**
		 * Validates If Saved Version is same as in the class version.
		 *
		 * @param        $value
		 * @param string $type
		 *
		 * @return bool|mixed
		 */
		protected function validate_version( $value, $type = '' ) {
			if ( false === $value || empty( $value ) || is_null( $value ) ) {
				return false;
			}

			$key = ( 'option' === $type ) ? 'option_version' : 'transient_version';
			return version_compare( $this->option( $key ), $value, '=' );
		}

		/**
		 * @param bool  $key
		 * @param array $args
		 *
		 * @static
		 * @return \Varunsridharan\WordPress\Transient_Api
		 */
		public static function instance( $key = false, $args = array() ) {
			$key = ( false === $key ) ? static::class : $key;
			if ( ! isset( self::$_instances[ $key ] ) ) {
				self::$_instances[ $key ] = new static( $args );
			}
			return self::$_instances[ $key ];
		}

		/**
		 * @param string $key
		 * @param string $value
		 * @param string $expiry
		 *
		 * @return mixed
		 */
		public function force_set( $key = '', $value = '', $expiry = '' ) {
			if ( true === $this->option( 'is_option' ) ) {
				return $this->update_option( $key, $value, $expiry );
			}
			$this->delete_transient( $key );
			return $this->set_transient( $key, $value, $expiry );
		}

		/**
		 * @param        $key
		 * @param        $value
		 * @param string $status
		 *
		 * @return bool
		 */
		public function update_option( $key, $value, $status = '' ) {
			$key         = $this->key( $key, true );
			$version_key = $this->get_version_key( $key );
			$_status     = $this->wp_update_option( $key, $value, $status );
			$this->wp_update_option( $version_key, $this->option( 'option_version' ), $status );
			return $_status;
		}

		/**
		 * @param $_key
		 *
		 * @return bool
		 */
		public function delete_transient( $_key ) {
			$key         = $this->key( $_key, false );
			$version_key = $this->get_version_key( $key );
			if ( $this->wp_delete_transient( $key ) ) {
				return ( $this->wp_delete_transient( $version_key ) ) ? true : false;
			}
			return false;
		}

		/**
		 * @param     $_key
		 * @param     $value
		 * @param int $expiry
		 *
		 * @return bool
		 */
		public function set_transient( $_key, $value, $expiry = 0 ) {
			$key         = $this->key( $_key, false );
			$version_key = $this->get_version_key( $key );
			$_status     = $this->wp_set_transient( $key, $value, $expiry );
			$this->wp_set_transient( $version_key, $this->option( 'option_version' ), $expiry );
			return $_status;
		}

		/**
		 * @param string $key
		 * @param string $value
		 * @param string $expiry
		 *
		 * @return bool
		 */
		public function set( $key = '', $value = '', $expiry = '' ) {
			return ( true === $this->option( 'is_option' ) ) ? $this->set_option( $key, $value, $expiry ) : $this->set_transient( $key, $value, $expiry );
		}

		/**
		 * @param        $_key
		 * @param        $value
		 * @param string $status
		 *
		 * @return bool
		 */
		public function set_option( $_key, $value, $status = '' ) {
			$key         = $this->key( $_key, true );
			$version_key = $this->get_version_key( $key );
			$_status     = $this->wp_add_option( $key, $value, $status );
			$this->wp_add_option( $version_key, $this->option( 'option_version' ), $status );
			return $_status;
		}

		/**
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function get( $key = '' ) {
			return ( true === $this->option( 'is_option' ) ) ? $this->get_option( $key ) : $this->get_transient( $key );
		}

		/**
		 * @param $_key
		 *
		 * @return bool|mixed
		 */
		public function get_option( $_key ) {
			$key         = $this->key( $_key, true );
			$version_key = $this->get_version_key( $key );
			$version     = $this->wp_get_option( $version_key, true );
			if ( $this->validate_version( $version, 'option' ) === false ) {
				$this->delete_version_issue( $_key );
				return false;
			}
			return $this->wp_get_option( $key );
		}

		/**
		 * @param $_key
		 *
		 * @return bool|mixed
		 */
		public function get_transient( $_key ) {
			$key         = $this->key( $_key, false );
			$version_key = $this->get_version_key( $key );
			$version     = $this->wp_get_transient( $version_key );
			if ( $this->validate_version( $version, 'transient' ) === false ) {
				$this->delete_version_issue( $_key, 'transient' );
				return false;
			}
			return $this->wp_get_transient( $key );
		}

		/**
		 * @param $key
		 *
		 * @return bool|void
		 */
		public function delete( $key ) {
			return ( true === $this->option( 'is_option' ) ) ? $this->delete_option( $key ) : $this->delete_transient( $key );
		}

		/**
		 * @param $_key
		 *
		 * @return bool
		 */
		public function delete_option( $_key ) {
			$key         = $this->key( $_key, true );
			$version_key = $this->get_version_key( $key );
			if ( $this->wp_delete_option( $key ) ) {
				return ( $this->wp_delete_option( $version_key ) ) ? true : false;
			}
			return false;
		}

		/**
		 * @param        $key
		 * @param string $value
		 * @param string $expiry
		 *
		 * @return bool
		 */
		public function update( $key, $value = '', $expiry = '' ) {
			return ( $this->option( 'is_option' ) ) ? $this->update_option( $key, $value, $expiry ) : true;
		}

		/**
		 * Deletes if cache has any issues.
		 *
		 * @param        $key
		 * @param string $type
		 *
		 * @return bool
		 */
		protected function delete_version_issue( $key, $type = '' ) {
			if ( true === $this->option( 'option_auto_delete' ) && 'option' === $type ) {
				$this->delete_option( $key );
			}

			if ( true === $this->option( 'transient_auto_delete' ) ) {
				return $this->delete_transient( $key );
			}
			return false;
		}
	}
}
