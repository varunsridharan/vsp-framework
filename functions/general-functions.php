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

if ( ! function_exists( 'vsp_ajax_action' ) ) {
	/**
	 * Check if current request has action parameter and returns it
	 *
	 * @return bool
	 */
	function vsp_ajax_action() {
		if ( vsp_is_request( 'ajax' ) ) {
			return ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : false;
		}

		return false;
	}
}

if ( ! function_exists( 'vsp_is_ajax_heartbeat' ) ) {
	/**
	 * Checks if current request is heartbeat
	 *
	 * @return bool
	 */
	function vsp_is_ajax_heartbeat() {
		return ( vsp_is_ajax() === true && vsp_is_ajax( 'heartbeat' ) === true ) ? true : false;
	}
}

if ( ! function_exists( 'vsp_is_ajax' ) ) {
	/**
	 * Checks if current request is ajax
	 * Also takes required action key to check if the ajax is exactly the action is passed
	 *
	 * @param string $action .
	 *
	 * @return bool
	 */
	function vsp_is_ajax( $action = '' ) {
		if ( empty( $action ) ) {
			return vsp_is_request( 'ajax' );
		}

		return ( vsp_ajax_action() !== false && vsp_ajax_action() === $action ) ? true : false;
	}
}

if ( ! function_exists( 'vsp_is_cron' ) ) {
	/**
	 * Checks if current request is cron
	 *
	 * @return bool
	 */
	function vsp_is_cron() {
		return vsp_is_request( 'cron' );
	}
}

if ( ! function_exists( 'vsp_is_admin' ) ) {
	/**
	 * Checks if current request is admin
	 *
	 * @return bool
	 */
	function vsp_is_admin() {
		return vsp_is_request( 'admin' );
	}
}

if ( ! function_exists( 'vsp_is_frontend' ) ) {
	/**
	 * Checks if current request is frontend
	 *
	 * @return bool
	 */
	function vsp_is_frontend() {
		return vsp_is_request( 'frontend' );
	}
}

if ( ! function_exists( 'vsp_is_request' ) ) {
	/**
	 * Checks What kind of request is it.
	 *
	 * @param string $type .
	 *
	 * @return bool
	 */
	function vsp_is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_current_screen' ) ) {
	/**
	 * Gets current screen ID
	 *
	 * @param bool $only_id .
	 *
	 * @return bool|null|string|\WP_Screen
	 */
	function vsp_current_screen( $only_id = true ) {
		$screen = get_current_screen();
		if ( false === $only_id ) {
			return $screen;
		}

		return isset( $screen->id ) ? $screen->id : false;
	}
}

if ( ! function_exists( 'vsp_is_screen' ) ) {
	/**
	 * Checks if current screen is given screen
	 *
	 * @param string $check_screen .
	 * @param string $current_screen .
	 *
	 * @return bool
	 */
	function vsp_is_screen( $check_screen = '', $current_screen = '' ) {
		if ( empty( $check_screen ) ) {
			return false;
		}

		if ( empty( $current_screen ) ) {
			$current_screen = vsp_current_screen( true );
		}

		if ( is_array( $check_screen ) ) {
			if ( in_array( $current_screen, $check_screen, true ) ) {
				return true;
			}
		}

		if ( is_string( $check_screen ) ) {
			if ( $check_screen === $current_screen ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_fix_slug' ) ) {
	/**
	 * Fix Slug
	 *
	 * @param string $name .
	 *
	 * @return string
	 */
	function vsp_fix_slug( $name ) {
		$name = ltrim( $name, ' ' );
		$name = ltrim( $name, '_' );
		$name = rtrim( $name, ' ' );
		$name = rtrim( $name, '_' );
		return $name;
	}
}

if ( ! function_exists( 'vsp_addons_extract_tags' ) ) {
	/**
	 * Extracts Addon Tags
	 *
	 * @param string $content .
	 * @param bool   $is_addons_reqplugin .
	 *
	 * preg_match_all( '@\[([^<>&\[\]\x00-\x20=]++)@',$content, $reg_shortcodes ).
	 *
	 * @return mixed
	 */
	function vsp_addons_extract_tags( $content, $is_addons_reqplugin = false ) {
		if ( false === $is_addons_reqplugin ) {
			preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $reg_shortcodes );
		} else {
			preg_match_all( '@\[(\w[^<>&\[\]\x00-\x20=]++)@', $content, $reg_shortcodes );
		}
		return $reg_shortcodes;
	}
}

if ( ! function_exists( 'vsp_addons_extract_tags_pattern' ) ) {
	/**
	 * Extract Tags
	 *
	 * @param array|string $tags .
	 * @param string       $content .
	 * @param bool         $is_addon .
	 *
	 * @return mixed
	 */
	function vsp_addons_extract_tags_pattern( $tags, $content, $is_addon = false ) {
		if ( ! is_array( $tags ) ) {
			$tags = array( $tags );
		}

		foreach ( $tags as $i => $tag ) {
			$tags[ $i ] = str_replace( '/', '\/', $tag );
		}

		$patterns = vsp_get_shortcode_regex( $tags, $is_addon );
		preg_match( "/$patterns/", $content, $data );
		return $data;
	}
}

if ( ! function_exists( 'vsp_current_page_url' ) ) {
	/**
	 * Returns Current Page URL
	 *
	 * @return string
	 */
	function vsp_current_page_url() {
		$pageURL = 'http';
		if ( isset( $_SERVER['HTTPS'] ) AND 'on' === $_SERVER['HTTPS'] ) {
			$pageURL .= 's';
		}

		$pageURL .= '://';

		if ( isset( $_SERVER['SERVER_PORT'] ) AND '80' !== $_SERVER['SERVER_PORT'] ) {
			$pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		} else {
			$pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}

		return $pageURL;
	}
}

if ( ! function_exists( 'vsp_get_time_in_seconds' ) ) {
	/**
	 * Returns Cache Time in numeric values
	 *
	 * @param string $time .
	 *
	 * @return float|int
	 */
	function vsp_get_time_in_seconds( $time ) {
		$times = explode( '_', $time );
		if ( ! is_array( $times ) ) {
			return $time;
		}

		$time_limit = $times[0];
		$type       = $times[1];

		$time_limit = intval( $time_limit );

		switch ( $type ) {
			case 'seconds':
			case 'second':
			case 'sec':
				$time = $time_limit;
				break;
			case 'minute':
			case 'minutes':
			case 'min':
				$time = $time_limit * MINUTE_IN_SECONDS;
				break;
			case 'hour':
			case 'hours':
			case 'hrs':
				$time = $time_limit * HOUR_IN_SECONDS;
				break;
			case 'days':
			case 'day':
				$time = $time_limit * DAY_IN_SECONDS;
				break;
			case 'weeks':
			case 'week':
				$time = $time_limit * WEEK_IN_SECONDS;
				break;

			case 'month':
			case 'months':
				$time = $time_limit * MONTH_IN_SECONDS;
				break;
			case 'year':
			case 'years':
				$time = $time_limit * YEAR_IN_SECONDS;
				break;
		}

		return intval( $time );
	}
}

if ( ! function_exists( 'vsp_cdn_url' ) ) {
	/**
	 * Returns CDN URL
	 *
	 * @return string
	 */
	function vsp_cdn_url() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			return 'https://varunsridharan.github.io/vs-plugins-cdn-dev/';
		} else {
			return 'https://varunsridharan.github.io/vs-plugins-cdn/';
		}
	}
}

if ( ! function_exists( 'vsp_get_cdn' ) ) {
	/**
	 * Gets CDN Data.
	 *
	 * @param string $part_url .
	 * @param bool   $force_decode .
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	function vsp_get_cdn( $part_url, $force_decode = false ) {
		$part_url = ltrim( $part_url, '/' );
		$url      = vsp_cdn_url() . $part_url;
		$resource = wp_remote_get( $url );

		if ( is_wp_error( $resource ) ) {
			return $resource;
		} else {
			$body = wp_remote_retrieve_body( $resource );
			return json_decode( $body, $force_decode );
		}
	}
}

if ( ! function_exists( 'vsp_js_vars_encode' ) ) {
	/**
	 * Encodes PHP Array in JSString.
	 *
	 * @param array $l10n .
	 *
	 * @return array|string
	 */
	function vsp_js_vars_encode( $l10n ) {
		if ( is_array( $l10n ) ) {
			foreach ( (array) $l10n as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		} else {
			$l10n = html_entity_decode( (string) $l10n, ENT_QUOTES, 'UTF-8' );
		}
		return $l10n;
	}
}

if ( ! function_exists( 'vsp_js_vars' ) ) {
	/**
	 * Generates Script Tag
	 *
	 * @param string $object_name .
	 * @param array  $l10n .
	 * @param bool   $with_script_tag .
	 *
	 * @return string
	 */
	function vsp_js_vars( $object_name, $l10n, $with_script_tag = true ) {
		$l10n = vsp_js_vars_encode( $l10n );

		$script = 'var ' . $object_name . ' = ' . wp_json_encode( $l10n ) . ';';
		if ( ! empty( $after ) ) {
			$script .= "\n$after;";
		}

		if ( $with_script_tag ) {
			$script = '<script type="text/javascript">' . $script . '</script>';
		}
		return $script;
	}
}

if ( ! function_exists( 'vsp_placeholder_img' ) ) {
	/**
	 * Returns VSP Placeholder Image
	 *
	 * @return mixed
	 */
	function vsp_placeholder_img() {
		return apply_filters( 'vsp_placeholder_img', vsp_img( 'noimage.png' ) );
	}
}

if ( ! function_exists( 'vsp_is_user_role' ) ) {
	/**
	 * Checks if given user role is the same as current user role
	 *
	 * @param null $role .
	 * @param null $current_role .
	 *
	 * @return bool
	 */
	function vsp_is_user_role( $role = null, $current_role = null ) {
		if ( in_array( $role, array( 'logedout', 'loggedout', 'visitor' ), true ) ) {
			$role = 'visitor';
		}

		if ( null === $current_role ) {
			$current_role = vsp_get_current_user( true );
		}

		return ( $role === $current_role ) ? true : false;
	}
}

if ( ! function_exists( 'vsp_get_current_user' ) ) {
	/**
	 * Gets current user information.
	 *
	 * @param bool $user_role_only .
	 *
	 * @return mixed|string|\WP_User
	 */
	function vsp_get_current_user( $user_role_only = true ) {
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
}

if ( ! function_exists( 'vsp_get_current_user_id' ) ) {
	/**
	 * Gets current wp user id.
	 *
	 * @return int
	 */
	function vsp_get_current_user_id() {
		return get_current_user_id();
	}
}

if ( ! function_exists( 'vsp_wp_user_roles' ) ) {
	/**
	 * Gets all wp user roles
	 *
	 * @return array
	 */
	function vsp_wp_user_roles() {
		$all_roles = array();
		if ( function_exists( 'wp_roles' ) ) {
			$all_roles = wp_roles()->roles;
		}
		return $all_roles;
	}
}

if ( ! function_exists( 'vsp_get_user_roles' ) ) {
	/**
	 * Gets all wp user roles with visitor role.
	 *
	 * @return array|mixed
	 */
	function vsp_get_user_roles() {
		$user_roles            = vsp_wp_user_roles();
		$user_roles['visitor'] = array( 'name' => __( 'Visitor / Logged-Out User', 'vsp-framework' ) );
		$user_roles            = apply_filters( 'wc_rbp_wp_user_roles', $user_roles );
		return $user_roles;
	}
}

if ( ! function_exists( 'vsp_user_roles_as_options' ) ) {
	/**
	 * Returns only user roles as options or just a array of slug.
	 *
	 * @param bool $only_slug .
	 *
	 * @return array
	 */
	function vsp_user_roles_as_options( $only_slug = false ) {
		$return = array();
		foreach ( vsp_get_user_roles() as $slug => $data ) {
			$return[ $slug ] = $data['name'];
		}
		return ( true === $only_slug ) ? array_keys( $return ) : $return;
	}
}

if ( ! function_exists( 'vsp_filter_user_roles' ) ) {
	/**
	 * This function will filter vsp_user_roles_as_options function and provide only the given user role slug values
	 *
	 * @param array $required .
	 *
	 * @return array
	 */
	function vsp_filter_user_roles( $required = array() ) {

		$existing = vsp_user_roles_as_options( false );
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
}

if ( ! function_exists( 'vsp_array_insert_before' ) ) {
	/**
	 * Inserts a new key/value before the key in the array.
	 *
	 * @param string $key The key to insert before.
	 * @param array  $array An array to insert in to.
	 * @param string $new_key The key to insert.
	 * @param mixed  $new_value An value to insert.
	 *
	 * @return array|boolean|bool The new array if the key exists, FALSE otherwise.
	 */
	function vsp_array_insert_before( $key, array &$array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
				$new[ $k ] = $value;
			}
			return $new;
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_array_insert_after' ) ) {
	/**
	 * Inserts a new key/value after the key in the array.
	 *
	 * @param string $key The key to insert after.
	 * @param array  $array An array to insert in to.
	 * @param string $new_key The key to insert.
	 * @param mixed  $new_value An value to insert.
	 *
	 * @return array|mixed The new array if the key exists, FALSE otherwise.
	 */
	function vsp_array_insert_after( $key, array &$array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
			}
			return $new;
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_array_to_html_attributes' ) ) {
	/**
	 * Converts PHP Array into html attributes
	 *
	 * @param array $array .
	 *
	 * @return string
	 */
	function vsp_array_to_html_attributes( $array = array() ) {
		$attrs = '';
		if ( ! empty( $array ) ) {
			foreach ( $array as $id => $val ) {
				$attrs = ' ' . esc_attr( $id ) . '="' . esc_attr( $val ) . '" ';
			}
		}
		return $attrs;
	}
}

if ( ! function_exists( 'vsp_validate_css_unit' ) ) {
	/**
	 * Validates CSS Units.
	 *
	 * @param string $value .
	 *
	 * @return string
	 */
	function vsp_validate_css_unit( $value ) {
		$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
		// allowed metrics: http://www.w3schools.com/cssref/css_units.asp.
		preg_match( $pattern, $value, $matches );
		$value = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
		$unit  = isset( $matches[2] ) ? $matches[2] : 'px';
		return $value . $unit;
	}
}

if ( ! function_exists( 'vsp_print_r' ) ) {
	/**
	 * Simple Debug Function
	 *
	 * @uses \print_r()
	 *
	 * @param mixed $debug .
	 * @param bool  $is_exit .
	 */
	function vsp_print_r( $debug, $is_exit = false ) {
		echo '<pre>' . print_r( $debug, true ) . '</pre>';
		if ( $is_exit ) {
			exit;
		}
	}
}

if ( ! function_exists( 'vsp_send_json_callback' ) ) {
	/**
	 * Send Json Callback array in ajax.
	 * used for sweatalert / trigger custom js functions.
	 *
	 * @param bool  $status .
	 * @param array $functions .
	 * @param array $other_info .
	 * @param null  $status_code .
	 */
	function vsp_send_json_callback( $status = true, $functions = array(), $other_info = array(), $status_code = null ) {
		$function = 'wp_send_json_error';
		if ( $status ) {
			$function = 'wp_send_json_success';
		}

		if ( is_string( $functions ) ) {
			$functions = array( $functions );
		}

		$data = array(
			'callback' => $functions,
		);

		$data = array_merge( $data, $other_info );
		$function( $data, $status_code );
	}
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @param int $limit Time limit.
 */
function vsp_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}
