<?php

namespace VSP\Core\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait VSP_Framework_Array_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
trait Array_Helper {
	/**
	 * Converts JSON String To Array
	 *
	 * @param string $data
	 * @param bool   $to_object if Set To true then it returns as object or array
	 *
	 * @return array|bool
	 */
	public static function json_to( $data = '', $to_object = false ) {
		$json = json_decode( $data, $to_object );
		return ( null === $json ) ? false : $json;
	}

	/**
	 * Groups the elements of an array based on the given function.
	 *
	 * @param $items
	 * @param $group_callback_function
	 *
	 * @return array
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
	 * @return array|object $output Merged user defined values with defaults.
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

	/**
	 * Filters An Array based on the given value
	 *
	 * @param array $required
	 * @param array $existing
	 *
	 * @return array
	 * @example
	 * $required = array('somekey1','somekey2');
	 * $existing = array('somekey1'=>'OMG','somekey2'=>"EOO",'somekey3'=>'okclose');
	 * $return = array('somekey1' => 'OMG','somekey2' => "EOO");
	 */
	public static function filter_array_data( $required, $existing ) {
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

