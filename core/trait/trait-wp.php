<?php
/**
 * VSP WP Trait
 *
 * Created by PhpStorm.
 * User: varun
 * Date : 13-10-2018
 * Time : 01:42 PM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/trait
 * @copyright GPL V3 Or greater
 */

/**
 * Trait VSP_Framework_WP_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait VSP_WP_Trait {
	/**
	 * user_roles
	 *
	 * @var array
	 */
	protected static $user_roles = array();

	/**
	 * Checks if given user role is the same as current user role
	 *
	 * @param null $role
	 * @param null $current_role
	 *
	 * @return bool
	 * @static
	 */
	public static function is_user_role( $role = null, $current_role = null ) {
		if ( in_array( $role, array( 'logedout', 'loggedout', 'visitor' ), true ) ) {
			$role = 'visitor';
		}

		if ( null === $current_role ) {
			$current_role = vsp_get_current_user( true );
		}

		return ( $role === $current_role ) ? true : false;
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
		$existing = self::user_roles_lists( false );
		if ( ! is_array( $required ) ) {
			return $existing;
		}
		foreach ( $existing as $slug => $name ) {
			if ( ! in_array( $slug, $required, true ) ) {
				unset( $existing[ $slug ] );
			}
		}
		return $existing;
	}

	/**
	 * Returns only user roles as options or just a array of slug.
	 *
	 * @param bool $only_slug .
	 *
	 * @return array
	 */
	public static function user_roles_lists( $only_slug = false ) {
		$return = array();
		foreach ( self::get_user_roles() as $slug => $data ) {
			$return[ $slug ] = $data['name'];
		}
		return ( true === $only_slug ) ? array_keys( $return ) : $return;
	}

	/**
	 * @param bool $only_wp
	 *
	 * @return array
	 * @static
	 */
	public static function get_user_roles( $only_wp = false ) {
		if ( empty( self::$user_roles ) ) {
			self::$user_roles = array();
			if ( function_exists( 'wp_roles' ) ) {
				self::$user_roles            = wp_roles()->roles;
				self::$user_roles['visitor'] = array( 'name' => __( 'Visitor / Logged-Out User', 'vsp-framework' ) );
			}
		}

		if ( true === $only_wp ) {
			$roles = self::$user_roles;
			unset( $roles['visitor'] );
			return $roles;
		}
		return self::$user_roles;
	}

	/**
	 * Gets the current WordPress site name.
	 *
	 * This is helpful for retrieving the actual site name instead of the
	 * network name on multisite installations.
	 *
	 * @since 4.6.0
	 * @return string
	 */
	public static function get_site_name() {
		return ( is_multisite() ) ? get_blog_details()->blogname : get_bloginfo( 'name' );
	}
}
