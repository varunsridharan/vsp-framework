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
	 * @uses trailingslashit
	 *
	 * @return string
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
	 * @param     $cache_name .
	 * @param     $data .
	 * @param int $expiry .
	 *
	 * @return bool
	 */
	function vsp_set_cache( $cache_name, $data, $expiry = 0 ) {
		$expiry = vsp_get_time_in_seconds( $expiry );
		return set_transient( $cache_name, $data, $expiry );
	}
}

if ( ! function_exists( 'vsp_get_cache' ) ) {
	/**
	 * @param $cache_name .
	 *
	 * @return mixed
	 */
	function vsp_get_cache( $cache_name ) {
		return get_transient( $cache_name );
	}
}

if ( ! function_exists( 'vsp_delete_cache' ) ) {
	/**
	 * @param $cache_name .
	 *
	 * @return bool
	 */
	function vsp_delete_cache( $cache_name ) {
		return delete_transient( $cache_name );
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

if ( ! function_exists( 'vsp_update_term_meta' ) ) {
	/**
	 * @param        $term_id .
	 * @param        $meta_key .
	 * @param        $meta_value .
	 * @param string $prev_value .
	 *
	 * @return bool|int|\WP_Error
	 */
	function vsp_update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		return function_exists( 'update_term_meta' ) ? update_term_meta( $term_id, $meta_key, $meta_value, $prev_value ) : update_option( 'vsp_tm_' . $term_id . '_' . $meta_key, $meta_value );
	}
}

if ( ! function_exists( 'vsp_add_term_meta' ) ) {
	/**
	 * @param      $term_id .
	 * @param      $meta_key .
	 * @param      $meta_value .
	 * @param bool $unique .
	 *
	 * @return bool|int|\WP_Error
	 */
	function vsp_add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
		return function_exists( 'add_term_meta' ) ? add_term_meta( $term_id, $meta_key, $meta_value, $unique ) : add_option( 'vsp_tm_' . $term_id . '_' . $meta_key, $meta_value );
	}
}

if ( ! function_exists( 'vsp_delete_term_meta' ) ) {
	/**
	 * @param        $term_id .
	 * @param        $meta_key .
	 * @param string $meta_value .
	 * @param bool   $deprecated .
	 *
	 * @return bool
	 */
	function vsp_delete_term_meta( $term_id, $meta_key, $meta_value = '', $deprecated = false ) {
		return function_exists( 'delete_term_meta' ) ? delete_term_meta( $term_id, $meta_key, $meta_value ) : delete_option( 'vsp_tm_' . $term_id . '_' . $meta_key );
	}
}

if ( ! function_exists( 'vsp_get_term_meta' ) ) {
	/**
	 * @param      $term_id .
	 * @param      $key .
	 * @param bool $single .
	 *
	 * @return mixed
	 */
	function vsp_get_term_meta( $term_id, $key, $single = true ) {
		return function_exists( 'get_term_meta' ) ? get_term_meta( $term_id, $key, $single ) : get_option( 'vsp_tm_' . $term_id . '_' . $key );
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
		return VSP_Helper::current_user( $user_role_only );
	}
}

if ( ! function_exists( 'vsp_get_current_user_id' ) ) {
	/**
	 * Gets current wp user id.
	 *
	 * @return int
	 */
	function vsp_get_current_user_id() {
		return VSP_Helper::current_user_id();
	}
}

if ( ! function_exists( 'vsp_wp_user_roles' ) ) {
	/**
	 * Gets all wp user roles
	 *
	 * @return array
	 */
	function vsp_wp_user_roles() {
		return VSP_Helper::get_user_roles();
	}
}
