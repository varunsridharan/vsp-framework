<?php
/**
 * VSP Framework Trait
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

namespace VSP\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Trait VSP_Framework_Array_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait Array_Helper {
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
	public static function array_insert_before( $key, array &$array, $new_key, $new_value ) {
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
	public static function array_insert_after( $key, array &$array, $new_key, $new_value ) {
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

	/**
	 * Converts PHP Array into html attributes
	 *
	 * @param array $array .
	 *
	 * @return string
	 */
	public static function array_to_html_attr( $array = array() ) {
		$attrs = '';
		if ( ! empty( $array ) ) {
			foreach ( $array as $id => $val ) {
				$attrs = ' ' . esc_attr( $id ) . '="' . esc_attr( $val ) . '" ';
			}
		}
		return $attrs;
	}

	/**
	 * Converts JSON String To Array
	 *
	 * @param string $data
	 *
	 * @return array|bool
	 * @static
	 */
	public static function json_to_array( $data = '' ) {
		$json = json_decode( $data, true );
		return ( null === $json ) ? false : $json;
	}

	/**
	 * Converts JSON String To Object
	 *
	 * @param string $data
	 *
	 * @return bool|object
	 * @static
	 */
	public static function json_to_object( $data = '' ) {
		$json = json_decode( $data );
		return ( null === $json ) ? false : $json;
	}

	/**
	 * Safely get and trim data from $_POST
	 *
	 * @param string $key array key to get from $_POST array.
	 * @param mixed  $default
	 *
	 * @return string value from $_POST or blank string if $_POST[ $key ] is not set
	 * @since 3.0.0
	 *
	 */
	public static function get_post( $key, $default = false ) {
		if ( isset( $_POST[ $key ] ) ) {
			return trim( $_POST[ $key ] );
		}
		return $default;
	}

	/**
	 * Safely get and trim data from $_REQUEST
	 *
	 * @param string $key array key to get from $_REQUEST array.
	 * @param mixed  $default
	 *
	 * @return string value from $_REQUEST or blank string if $_REQUEST[ $key ] is not set
	 * @since 3.0.0
	 *
	 */
	public static function get_request( $key, $default = false ) {
		if ( isset( $_REQUEST[ $key ] ) ) {
			return trim( $_REQUEST[ $key ] );
		}
		return $default;
	}

	/**
	 * Groups the elements of an array based on the given function.
	 *
	 * @param $items
	 * @param $group_callback_function
	 *
	 * @return array
	 * @static
	 * @example array_group_by(['one', 'two', 'three'], 'strlen') // [3 => ['one', 'two'], 5 => ['three']]
	 */
	public static function array_group_by( $items, $group_callback_function ) {
		$group = [];
		foreach ( $items as $item ) {
			if ( ( ! is_string( $group_callback_function ) && is_callable( $group_callback_function ) ) || function_exists( $group_callback_function ) ) {
				$key             = call_user_func( $group_callback_function, $item );
				$group[ $key ][] = $item;
			} elseif ( is_object( $item ) ) {
				$group[ $item->{$group_callback_function} ][] = $item;
			} elseif ( isset( $item[ $group_callback_function ] ) ) {
				$group[ $item[ $group_callback_function ] ][] = $item;
			}
		}

		return $group;
	}

	/**
	 * Checks a flat list for duplicate values. Returns true if duplicate values exists and false if values are all unique.
	 *
	 * @param $items
	 *
	 * @return bool
	 * @static
	 */
	public static function array_has_duplicates( $items ) {
		return count( $items ) > count( array_unique( $items ) );
	}

	/**
	 * Like wp_parse_args but supports recursivity
	 * By default converts the returned type based on the $args and $defaults
	 *
	 * @param array|object $args Values to merge with $defaults.
	 * @param array|object $defaults Array, Object that serves as the defaults or string.
	 * @param boolean      $deep if set to true then it will do a deep merge.
	 * @param boolean      $preserve_type Optional. Convert output array into object if $args or $defaults if it is. Default true.
	 * @param boolean      $preserve_integer_keys Optional. If given, integer keys will be preserved and merged instead of appended.
	 *
	 * @return array|object  $output                 Merged user defined values with defaults.
	 */
	public static function parse_args( $args, $defaults, $deep = false, $preserve_type = true, $preserve_integer_keys = false ) {
		if ( false === $deep ) {
			return wp_parse_args( $args, $defaults );
		}

		$output = array();
		foreach ( array( $defaults, $args ) as $list ) {
			foreach ( (array) $list as $key => $value ) {
				if ( is_integer( $key ) && ! $preserve_integer_keys ) {
					$output[] = $value;
				} elseif ( isset( $output[ $key ] ) && ( is_array( $output[ $key ] ) || is_object( $output[ $key ] ) ) && ( is_array( $value ) || is_object( $value ) ) ) {
					$output[ $key ] = self::parse_args( $value, $output[ $key ], $deep, $preserve_type, $preserve_integer_keys );
				} else {
					$output[ $key ] = $value;
				}
			}
		}
		return ( $preserve_type && ( is_object( $args ) || is_object( $defaults ) ) ) ? (object) $output : $output;
	}
}

