<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vsp_slashit' ) ) {
	/**
	 * @param string $path .
	 *
	 * @return string
	 * @uses trailingslashit
	 *
	 */
	function vsp_slashit( $path ) {
		return trailingslashit( $path );
	}
}

if ( ! function_exists( 'vsp_unslashit' ) ) {
	/**
	 * @param string $path .
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
	 * @throws \WPOnion\Cache_Not_Found
	 */
	function vsp_get_cache( $key ) {
		return wponion_get_cache( $key );
	}
}

if ( ! function_exists( 'vsp_has_cache' ) ) {
	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	function vsp_has_cache( $key ) {
		return wponion_has_cache( $key );
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

if ( ! function_exists( 'vsp_fix_title' ) ) {
	/**
	 * @param $title .
	 *
	 * @return string
	 */
	function vsp_fix_title( $title ) {
		return sanitize_title( $title );
	}
}

if ( ! function_exists( 'vsp_get_current_user' ) ) {
	/**
	 * Gets current user information.
	 *
	 * @param bool $user_role_only
	 *
	 * @return mixed|string|\WP_User
	 */
	function vsp_get_current_user( $user_role_only = true ) {
		return \VSP\Helper::current_user( $user_role_only );
	}
}

if ( ! function_exists( 'vsp_get_current_user_id' ) ) {
	/**
	 * Gets current wp user id.
	 *
	 * @return int
	 */
	function vsp_get_current_user_id() {
		return \VSP\Helper::current_user_id();
	}
}

if ( ! function_exists( 'vsp_wp_user_roles' ) ) {
	/**
	 * Gets all wp user roles
	 *
	 * @return array
	 */
	function vsp_wp_user_roles() {
		return \VSP\Helper::get_user_roles();
	}
}
