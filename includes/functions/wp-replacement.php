<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vsp_slashit' ) ) {
	/**
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
	 * @param string $key
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
	 * @param string     $key
	 * @param bool|mixed $defaults
	 *
	 * @return bool|mixed
	 */
	function vsp_get_cache_defaults( $key, $defaults = false ) {
		return wponion_get_cache_defaults( $key, $defaults );
	}
}

if ( ! function_exists( 'vsp_get_cache' ) ) {
	/**
	 * @param string $key
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
	 * @param string $key
	 */
	function vsp_delete_cache( $key ) {
		wponion_delete_cache( $key );
	}
}
