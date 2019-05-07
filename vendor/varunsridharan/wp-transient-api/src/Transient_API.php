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

if ( ! class_exists( '\Varunsridharan\WordPress\Transient_WP_Api' ) ) {
	/**
	 * Class Transient_WP_Api
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Transient_WP_Api {
		/**
		 * is_option
		 *
		 * @var bool
		 */
		protected $is_option = false;

		/**
		 * transient_limit
		 *
		 * @var int
		 */
		protected $transient_limit = 170;

		/**
		 * option_limit
		 *
		 * @var int
		 */
		protected $option_limit = 190;

		/**
		 * option_prefix
		 *
		 * @var string
		 */
		protected $option_prefix = '';

		/**
		 * option_surfix
		 *
		 * @var string
		 */
		protected $option_surfix = '';

		/**
		 * transient_prefix
		 *
		 * @var string
		 */
		protected $transient_prefix = '';

		/**
		 * transient_surfix
		 *
		 * @var string
		 */
		protected $transient_surfix = '';

		/**
		 * option_version
		 *
		 * @var float
		 */
		protected $option_version = 1.0;

		/**
		 * transient_version
		 *
		 * @var float
		 */
		protected $transient_version = 1.0;

		/**
		 * option_auto_delete
		 *
		 * @var bool
		 */
		protected $option_auto_delete = false;

		/**
		 * transient_auto_delete
		 *
		 * @var bool
		 */
		protected $transient_auto_delete = false;

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
				return $this->option_prefix . $key . $this->option_surfix;
			}

			return $this->transient_prefix . $key . $this->transient_surfix;
		}

		/**
		 * Validates Key Length if key lenght exceeds it will md5 or returns the orginal key
		 *
		 * @param string $key
		 *
		 * @return string
		 * @uses \md5()
		 * @uses \VS_Transient_WP_Api::check_length()
		 *
		 */
		protected function validate_length( $key = '' ) {
			if ( $this->check_length( $key ) === false ) {
				return $this->validate_length( md5( $key ) );
			}
			return $key;
		}

		/**
		 * Returns if key is in correct length
		 * if Type set to option then it uses $this->option_limit for char limit
		 * or uses $this->transient_limit for char limit
		 *
		 * @param string $key
		 *
		 * @return bool
		 * @uses \VS_Transient_WP_Api::$transient_limit
		 * @uses \VS_Transient_WP_Api::$option_limit
		 *
		 */
		protected function check_length( $key = '' ) {
			if ( true === $this->is_option ) {
				return ( strlen( $this->get_key( $key ) ) > $this->option_limit ) ? false : true;
			}

			return ( strlen( $this->get_key( $key ) ) > $this->transient_limit ) ? false : true;
		}

		/**
		 * Adds Given value to db using add_option
		 *
		 * @param string $key
		 * @param mixed  $value
		 * @param string $autoload
		 *
		 * @return bool
		 * @uses \add_option()
		 *
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
		 * @uses \update_option()
		 *
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
		 * @uses \delete_option()
		 *
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
		 * @uses \get_option()
		 *
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
		 * @uses \set_transient()
		 *
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
		 * @uses \get_transient()
		 *
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
		 * @uses \delete_transient()
		 *
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
		 * @uses \VS_Transient_WP_Api::$transient_version
		 *
		 * @uses \VS_Transient_WP_Api::$option_version
		 */
		protected function validate_version( $value, $type = '' ) {
			if ( false === $value || empty( $value ) || is_null( $value ) ) {
				return false;
			}

			if ( 'option' === $type ) {
				return version_compare( $this->option_version, $value, '=' );
			}

			return version_compare( $this->transient_version, $value, '=' );
		}
	}
}

if ( ! class_exists( '\Varunsridharan\WordPress\Transient_Api' ) ) {
	/**
	 * Class Transient_Api
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Transient_Api extends Transient_WP_Api {
		/**
		 * _instances
		 *
		 * @var array
		 */
		protected static $_instances = array();

		/**
		 * Creates & Returns A Static Instance.
		 *
		 * @static
		 * @return \Varunsridharan\WordPress\Transient_Api
		 */
		public static function instance() {
			if ( ! isset( self::$_instances[ static::class ] ) ) {
				self::$_instances[ static::class ] = new static();
			}
			return self::$_instances[ static::class ];
		}

		/**
		 * @param string $key
		 * @param string $value
		 * @param string $expiry
		 *
		 * @return mixed
		 */
		public function force_set( $key = '', $value = '', $expiry = '' ) {
			if ( true === $this->is_option ) {
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
			$this->wp_update_option( $version_key, $this->option_version, $status );
			return $_status;
		}

		/**
		 * @param $_key
		 */
		public function delete_transient( $_key ) {
			$key         = $this->key( $_key, false );
			$version_key = $this->get_version_key( $key );
			$this->wp_delete_transient( $key );
			$this->wp_delete_transient( $version_key );
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
			$this->wp_set_transient( $version_key, $this->option_version, $expiry );
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
			if ( true === $this->is_option ) {
				return $this->set_option( $key, $value, $expiry );
			}
			return $this->set_transient( $key, $value, $expiry );

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
			$this->wp_add_option( $version_key, $this->option_version, $status );
			return $_status;
		}

		/**
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function get( $key = '' ) {
			if ( true === $this->is_option ) {
				return $this->get_option( $key );
			}

			return $this->get_transient( $key );
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
		 */
		public function delete( $key ) {
			if ( true === $this->is_option ) {
				return $this->delete_option( $key );
			}
			return $this->delete_transient( $key );
		}

		/**
		 * @param $_key
		 */
		public function delete_option( $_key ) {
			$key         = $this->key( $_key, true );
			$version_key = $this->get_version_key( $key );
			$this->wp_delete_option( $key );
			$this->wp_delete_option( $version_key );
		}

		/**
		 * @param        $key
		 * @param string $value
		 * @param string $expiry
		 *
		 * @return bool
		 */
		public function update( $key, $value = '', $expiry = '' ) {
			if ( $this->is_option ) {
				return $this->update_option( $key, $value, $expiry );
			}
			return true;
		}

		/**
		 * Deletes if cache has any issues.
		 *
		 * @param        $key
		 * @param string $type
		 *
		 * @return mixed
		 */
		protected function delete_version_issue( $key, $type = '' ) {
			if ( true === $this->option_auto_delete && 'option' === $type ) {
				$this->delete_option( $key );
			}

			if ( true === $this->transient_auto_delete ) {
				return $this->delete_transient( $key );
			}
			return false;
		}
	}
}