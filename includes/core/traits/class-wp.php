<?php

namespace VSP\Core\Traits;

defined( 'ABSPATH' ) || exit;

use VSP\Helper;
use WPOnion\Exception\Cache_Not_Found;

/**
 * Trait VSP_Framework_WP_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
trait WP {
	/**
	 * Checks if given user role is the same as current user role
	 *
	 * @param null $role
	 * @param null $current_role
	 *
	 * @return bool
	 */
	public static function is_user_role( $role = null, $current_role = null ) {
		if ( in_array( $role, array( 'logedout', 'loggedout', 'visitor' ), true ) ) {
			$role = 'visitor';
		}

		if ( null === $current_role ) {
			$current_role = Helper::current_user( true );
		}

		return ( $role === $current_role );
	}

	/**
	 * Gets current user information.
	 *
	 * @param bool $user_role_only .
	 *
	 * @return mixed|string|\WP_User
	 */
	public static function current_user( $user_role_only = true ) {
		$user_role = wp_get_current_user();
		if ( true === $user_role_only ) {
			$user_roles = $user_role->roles;
			$user_role  = array_shift( $user_roles );
			if ( null === $user_role ) {
				$user_role = 'visitor';
			}
		}
		return $user_role;
	}

	/**
	 * Gets current wp user id.
	 *
	 * @return int
	 */
	public static function current_user_id() {
		return get_current_user_id();
	}

	/**
	 * This function will filter vsp_user_roles_as_options function and provide only the given user role slug values
	 *
	 * @param array $required .
	 *
	 * @return array
	 */
	public static function filter_roles( $required = array() ) {
		return self::filter_array_data( $required, self::user_roles_lists( false ) );
	}

	/**
	 * Returns only user roles as options or just a array of slug.
	 *
	 * @param bool $only_slug .
	 *
	 * @return array
	 */
	public static function user_roles_lists( $only_slug = false ) {
		try {
			$user_roles = vsp_get_cache( 'vsp/user_roles/options' );
		} catch ( Cache_Not_Found $exception ) {
			$user_roles = array();
			foreach ( self::get_user_roles() as $slug => $data ) {
				$user_roles[ $slug ] = $data['name'];
			}
			vsp_set_cache( 'vsp/user_roles/options', $user_roles );
		}
		return ( true === $only_slug ) ? array_keys( $user_roles ) : $user_roles;
	}

	/**
	 * Returns User Role's title for the given user slug.
	 *
	 * @param string|bool $slug
	 * @param string|bool $default
	 *
	 * @return mixed
	 */
	public static function user_role_title( $slug, $default = false ) {
		$roles = self::user_roles_lists();
		return ( isset( $roles[ $slug ] ) ) ? $roles[ $slug ] : $default;
	}

	/**
	 * @param bool $only_wp
	 *
	 * @return array
	 */
	public static function get_user_roles( $only_wp = false ) {
		try {
			$user_roles = vsp_get_cache( 'vsp/user_roles/list' );
		} catch ( Cache_Not_Found $exception ) {
			$user_roles = array();
			if ( function_exists( 'wp_roles' ) ) {
				$user_roles            = wp_roles()->roles;
				$user_roles['visitor'] = array( 'name' => __( 'Visitor / Logged-Out User', 'vsp-framework' ) );
			}
			vsp_set_cache( 'vsp/user_roles/list', $user_roles );
		}

		if ( true === $only_wp ) {
			unset( $user_roles['visitor'] );
			return $user_roles;
		}
		return $user_roles;
	}

	/**
	 * Gets the current WordPress site name.
	 *
	 * This is helpful for retrieving the actual site name instead of the
	 * network name on multisite installations.
	 *
	 * @return string
	 */
	public static function get_site_name() {
		return ( is_multisite() ) ? get_blog_details()->blogname : get_bloginfo( 'name' );
	}
}
