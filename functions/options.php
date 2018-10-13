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

global $vsp_vars_data;
$vsp_vars_data = array();

if ( ! function_exists( 'vsp_check_global_vars' ) ) {
	/**
	 * Creates / Returns a global variable for the given plugin slug
	 *
	 * @param string $plugin_name .
	 *
	 * @return mixed
	 */
	function &vsp_check_global_vars( $plugin_name = '' ) {
		$name = $plugin_name . '_plugin_data';
		if ( ! isset( $GLOBALS[ $name ] ) ) {
			$GLOBALS[ $name ] = array();
		}
		return $GLOBALS[ $name ];
	}
}

if ( ! function_exists( 'vsp_add_vars' ) ) {
	/**
	 * Adds Given Key & values to the plugin's global variable
	 *
	 * @param string $plugin_name .
	 * @param string $key .
	 * @param string $values .
	 * @param bool   $force_add .
	 *
	 * @return bool
	 */
	function vsp_add_vars( $plugin_name = '', $key = '', $values = '', $force_add = false ) {
		$variable =& vsp_check_global_vars( $plugin_name );
		if ( isset( $variable[ $key ] ) ) {
			if ( ! $force_add ) {
				return false;
			}
		}
		$variable[ $key ] = $values;
		return true;
	}
}

if ( ! function_exists( 'vsp_vars' ) ) {
	/**
	 * Returns plugin's global variable
	 *
	 * @param string $plugin_name .
	 * @param string $key .
	 * @param string $default .
	 *
	 * @return string|array
	 */
	function vsp_vars( $plugin_name = '', $key = '', $default = '' ) {
		$variable =& vsp_check_global_vars( $plugin_name );
		if ( ! isset( $variable[ $key ] ) ) {
			return $default;
		}
		return $variable[ $key ];
	}
}

if ( ! function_exists( 'vsp_remove_vars' ) ) {
	/**
	 * Removes a plugins global variable
	 *
	 * @param string $plugin_name .
	 * @param string $key .
	 *
	 * @return bool
	 */
	function vsp_remove_vars( $plugin_name = '', $key = '' ) {
		$variable =& vsp_check_global_vars( $plugin_name );
		if ( isset( $variable[ $key ] ) ) {
			unset( $variable[ $key ] );
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_option' ) ) {
	/**
	 * Returns VSP Options
	 *
	 * @param string $plugin_name .
	 * @param string $option_name .
	 * @param string $default .
	 *
	 * @return string
	 */
	function vsp_option( $plugin_name = '', $option_name = '', $default = '' ) {
		$options = vsp_vars( $plugin_name, 'settings', array() );
		if ( ! empty( $options ) ) {
			if ( 'all' === $option_name ) {
				return $options;
			}
			if ( isset( $options[ $option_name ] ) ) {
				return $options[ $option_name ];
			}
		}

		return $default;
	}
}
