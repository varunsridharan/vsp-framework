<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 01-03-2018
 * Time: 07:06 AM
 */

/**
 * Uses WP_Object_Cache With a simple wrapper to manage for all our plugins
 *
 * @use WP_Object_Cache
 * Class VSP_Cache
 */
Class VSP_Cache {

	public static function add( $key = '', $data = '', $expire = 0 ) {
		return wp_cache_add( $key, $data, self::group(), $expire );
	}

	public static function group() {
		return 'vsp_plugins';
	}

	public static function set( $key = '', $data = '', $expire = 0 ) {
		return wp_cache_set( $key, $data, self::group(), $expire );
	}

	public static function get( $key = '', &$is_found = false ) {
		return wp_cache_get( $key, self::group(), false, $is_found );
	}

	public static function delete( $key = '' ) {
		return wp_cache_delete( $key, self::group() );
	}

	public static function replace( $key = '', $data = '', $expire = 0 ) {
		return wp_cache_replace( $key, $data, self::group(), $expire );
	}
}