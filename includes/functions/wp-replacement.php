<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

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

if ( ! function_exists( 'vsp_register_script' ) ) {
	/**
	 * @param string $handle .
	 * @param string $src .
	 * @param array  $deps .
	 * @param string $ver .
	 * @param bool   $footer .
	 *
	 * @return bool
	 */
	function vsp_register_script( $handle = '', $src = '', $deps = array(), $ver = '1.0', $footer = true ) {
		$src = vsp_debug_file( $src );
		return wp_register_script( $handle, $src, $deps, $ver, $footer );
	}
}

if ( ! function_exists( 'vsp_register_style' ) ) {
	/**
	 * @param string $handle .
	 * @param string $src .
	 * @param array  $deps .
	 * @param string $ver .
	 * @param string $media .
	 *
	 * @return bool
	 */
	function vsp_register_style( $handle = '', $src = '', $deps = array(), $ver = '1.0', $media = 'all' ) {
		$src = vsp_debug_file( $src );
		return wp_register_style( $handle, $src, $deps, $ver, $media );
	}
}

if ( ! function_exists( 'vsp_load_script' ) ) {
	/**
	 * @param string $handle
	 * @param string $src
	 * @param array  $deps
	 * @param string $ver
	 * @param bool   $in_footer
	 */
	function vsp_load_script( $handle = '', $src = '', $deps = array(), $ver = '', $in_footer = false ) {
		$src = vsp_debug_file( $src );
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}
}

if ( ! function_exists( 'vsp_load_style' ) ) {
	/**
	 * @param string $handle .
	 * @param string $src .
	 * @param array  $deps .
	 * @param string $ver .
	 * @param bool   $in_footer .
	 */
	function vsp_load_style( $handle = '', $src = '', $deps = array(), $ver = '', $in_footer = false ) {
		$src = vsp_debug_file( $src );
		wp_enqueue_style( $handle, $src, $deps, $ver, $in_footer );
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
