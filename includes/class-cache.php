<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cache
 *
 * @package VSP\Core\Abstracts
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class Cache {
	/**
	 * Cache Key Prefix.
	 *
	 * @var string
	 */
	protected static $prefix = 'vsp';

	/**
	 * Handles Cache Key System.
	 *
	 * @param $key
	 *
	 * @return string
	 */
	protected static function cache_key( $key ) {
		return ( ! empty( static::$prefix ) ) ? static::$prefix . '/' . $key : $key;
	}

	/**
	 * Set Data Cache For Given Cache ID
	 *
	 * @param string $key cache_id.
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function set( $key, $value ) {
		return wponion_set_cache( static::cache_key( $key ), $value );
	}

	/**
	 * Set Cache With Defaults.
	 *
	 * @param string $key cache_id.
	 * @param mixed  $defaults
	 *
	 * @return bool|mixed
	 */
	public static function get_defaults( $key, $defaults = false ) {
		return wponion_get_cache_defaults( self::cache_key( $key ), $defaults );
	}

	/**
	 * Fetch & Returns Cached Data.
	 *
	 * @param string $key cache_id.
	 *
	 * @return mixed
	 * @throws \WPOnion\Exception\Cache_Not_Found
	 */
	public static function get( $key ) {
		return wponion_get_cache( self::cache_key( $key ) );
	}

	/**
	 * Deletes Cached Data For Given Key.
	 *
	 * @param string $key cache_id.
	 */
	public static function delete( $key ) {
		wponion_delete_cache( self::cache_key( $key ) );
	}
}
