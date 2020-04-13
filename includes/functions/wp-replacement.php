<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'vsp_slashit' ) ) {
	/**
	 * Appends a trailing slash.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	function vsp_slashit( $path ) {
		return trailingslashit( $path );
	}
}

if ( ! function_exists( 'vsp_unslashit' ) ) {
	/**
	 * Removes trailing forward slashes and backslashes if they exist.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	function vsp_unslashit( $path ) {
		return untrailingslashit( $path );
	}
}

if ( ! function_exists( 'vsp_set_cache' ) ) {
	/**
	 * Set Data Cache For Given Cache ID
	 *
	 * @param string $key cache_id.
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	function vsp_set_cache( $key, $value ) {
		return wponion_set_cache( $key, $value );
	}
}

if ( ! function_exists( 'vsp_get_cache_defaults' ) ) {
	/**
	 * Set Cache With Defaults.
	 *
	 * @param string $key cache_id.
	 * @param mixed  $defaults
	 *
	 * @return bool|mixed
	 */
	function vsp_get_cache_defaults( $key, $defaults = false ) {
		return wponion_get_cache_defaults( $key, $defaults );
	}
}

if ( ! function_exists( 'vsp_get_cache' ) ) {
	/**
	 * Fetch & Returns Cached Data.
	 *
	 * @param string $key cache_id.
	 *
	 * @return mixed
	 * @throws \WPOnion\Exception\Cache_Not_Found
	 */
	function vsp_get_cache( $key ) {
		return wponion_get_cache( $key );
	}
}

if ( ! function_exists( 'vsp_delete_cache' ) ) {
	/**
	 * Deletes Cached Data For Given Key.
	 *
	 * @param string $key cache_id.
	 */
	function vsp_delete_cache( $key ) {
		wponion_delete_cache( $key );
	}
}
