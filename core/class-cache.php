<?php
/**
 * VSP WP Cache Helper.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 01-03-2018
 * Time: 07:06 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

/**
 * Class VSP_Cache
 * Uses WP_Object_Cache With a simple wrapper to manage for all our plugins
 *
 * @use WP_Object_Cache
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class VSP_Cache {

	/**
	 * Adds A Cache Data
	 *
	 * @uses \wp_cache_add()
	 *
	 * @param string $key .
	 * @param string $data .
	 * @param int    $expire .
	 *
	 * @return bool
	 */
	public static function add( $key = '', $data = '', $expire = 0 ) {
		return wp_cache_add( $key, $data, self::group(), $expire );
	}

	/**
	 * Returns A WP Cache Group (UID)
	 *
	 * @return string
	 */
	public static function group() {
		return 'vsp_plugins';
	}

	/**
	 * Set cache
	 *
	 * @param string $key .
	 * @param string $data .
	 * @param int    $expire .
	 *
	 * @return bool
	 */
	public static function set( $key = '', $data = '', $expire = 0 ) {
		return wp_cache_set( $key, $data, self::group(), $expire );
	}

	/**
	 * Get cache
	 *
	 * @param string $key .
	 * @param bool   $is_found .
	 *
	 * @return bool|mixed
	 */
	public static function get( $key = '', &$is_found = false ) {
		return wp_cache_get( $key, self::group(), false, $is_found );
	}

	/**
	 * Delete cache
	 *
	 * @param string $key .
	 *
	 * @return bool
	 */
	public static function delete( $key = '' ) {
		return wp_cache_delete( $key, self::group() );
	}

	/**
	 * Replace cache
	 *
	 * @param string $key .
	 * @param string $data .
	 * @param int    $expire .
	 *
	 * @return bool
	 */
	public static function replace( $key = '', $data = '', $expire = 0 ) {
		return wp_cache_replace( $key, $data, self::group(), $expire );
	}
}
