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
	 * Converts Array To Json.
	 *
	 * @param array $array
	 *
	 * @return false|string
	 * @static
	 */
	public static function array_to_json( $array = array() ) {
		return wp_json_encode( $array );
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
	 * @since 3.0.0
	 *
	 * @param string $key array key to get from $_POST array.
	 * @param mixed  $default
	 *
	 * @return string value from $_POST or blank string if $_POST[ $key ] is not set
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
	 * @since 3.0.0
	 *
	 * @param string $key array key to get from $_REQUEST array.
	 * @param mixed  $default
	 *
	 * @return string value from $_REQUEST or blank string if $_REQUEST[ $key ] is not set
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
	 * @example array_group_by(['one', 'two', 'three'], 'strlen') // [3 => ['one', 'two'], 5 => ['three']]
	 * @return array
	 * @static
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
	 * @param  array|object $args Values to merge with $defaults.
	 * @param  array|object $defaults Array, Object that serves as the defaults or string.
	 * @param  boolean      $deep if set to true then it will do a deep merge.
	 * @param  boolean      $preserve_type Optional. Convert output array into object if $args or $defaults if it is. Default true.
	 * @param  boolean      $preserve_integer_keys Optional. If given, integer keys will be preserved and merged instead of appended.
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

	/**
	 * Recursively find a key's value in array
	 *
	 * @param string       $keys 'a/b/c'
	 * @param array|object $array_or_object
	 * @param null|mixed   $default_value
	 * @param string       $keys_delimiter
	 *
	 * @return null|mixed
	 */
	public static function akg( $keys, $array_or_object, $default_value = null, $keys_delimiter = '/' ) {
		if ( ! is_array( $keys ) ) {
			$keys = explode( $keys_delimiter, (string) $keys );
		}

		$array_or_object = ( vsp_is_callable( $array_or_object ) ) ? vsp_callback( $array_or_object ) : $array_or_object;
		$key_or_property = array_shift( $keys );

		if ( null === $key_or_property ) {
			return ( vsp_is_callable( $default_value ) ) ? vsp_callback( $default_value ) : $default_value;
		}

		$is_object = is_object( $array_or_object );

		if ( $is_object ) {
			if ( ! property_exists( $array_or_object, $key_or_property ) ) {
				return ( vsp_is_callable( $default_value ) ) ? vsp_callback( $default_value ) : $default_value;
			}
		} else {
			if ( ! is_array( $array_or_object ) || ! array_key_exists( $key_or_property, $array_or_object ) ) {
				return ( vsp_is_callable( $default_value ) ) ? vsp_callback( $default_value ) : $default_value;
			}
		}

		if ( isset( $keys[0] ) ) { // not used count() for performance reasons
			if ( $is_object ) {
				return self::akg( $keys, $array_or_object->{$key_or_property}, $default_value );
			} else {
				return self::akg( $keys, $array_or_object[ $key_or_property ], $default_value );
			}
		} else {
			if ( $is_object ) {
				return $array_or_object->{$key_or_property};
			} else {
				return $array_or_object[ $key_or_property ];
			}
		}
	}

	/**
	 * Set (or create if not exists) value for specified key in some array level
	 *
	 * @param string       $keys 'a/b/c', or 'a/b/c/' equivalent to: $arr['a']['b']['c'][] = $val;
	 * @param mixed        $value
	 * @param array|object $array_or_object
	 * @param string       $keys_delimiter
	 *
	 * @return array|object
	 */
	public static function aks( $keys, $value, &$array_or_object, $keys_delimiter = '/' ) {
		if ( ! is_array( $keys ) ) {
			$keys = explode( $keys_delimiter, (string) $keys );
		}

		$key_or_property = array_shift( $keys );
		if ( null === $key_or_property ) {
			return $array_or_object;
		}

		$is_object = is_object( $array_or_object );

		if ( $is_object ) {
			if ( ! property_exists( $array_or_object, $key_or_property ) || ! ( is_array( $array_or_object->{$key_or_property} ) || is_object( $array_or_object->{$key_or_property} ) ) ) {
				if ( '' === $key_or_property ) {
					// this happens when use 'empty keys' like: abc/d/e////i/j//foo/
					trigger_error( 'Cannot push value to object like in array ($arr[] = $val)', E_USER_WARNING );
				} else {
					$array_or_object->{$key_or_property} = array();
				}
			}
		} else {
			if ( ! is_array( $array_or_object ) ) {
				$array_or_object = array();
			}

			if ( ! array_key_exists( $key_or_property, $array_or_object ) || ! is_array( $array_or_object[ $key_or_property ] ) ) {
				if ( '' === $key_or_property ) {
					// this happens when use 'empty keys' like: abc.d.e....i.j..foo.
					$array_or_object[] = array();

					// get auto created key (last)
					end( $array_or_object );
					$key_or_property = key( $array_or_object );
				} else {
					$array_or_object[ $key_or_property ] = array();
				}
			}
		}

		if ( isset( $keys[0] ) ) { // not used count() for performance reasons
			if ( $is_object ) {
				self::aks( $keys, $value, $array_or_object->{$key_or_property} );
			} else {
				self::aks( $keys, $value, $array_or_object[ $key_or_property ] );
			}
		} else {
			if ( $is_object ) {
				$array_or_object->{$key_or_property} = $value;
			} else {
				$array_or_object[ $key_or_property ] = $value;
			}
		}

		return $array_or_object;
	}

	/**
	 * Unset specified key in some array level
	 *
	 * @param string       $keys 'a/b/c' -> unset($arr['a']['b']['c']);
	 * @param array|object $array_or_object
	 * @param string       $keys_delimiter
	 *
	 * @return array|object
	 */
	public static function aku( $keys, &$array_or_object, $keys_delimiter = '/' ) {
		if ( ! is_array( $keys ) ) {
			$keys = explode( $keys_delimiter, (string) $keys );
		}

		$key_or_property = array_shift( $keys );
		if ( null === $key_or_property || '' === $key_or_property ) {
			return $array_or_object;
		}

		$is_object = is_object( $array_or_object );

		if ( $is_object ) {
			if ( ! property_exists( $array_or_object, $key_or_property ) ) {
				return $array_or_object;
			}
		} else {
			if ( ! is_array( $array_or_object ) || ! array_key_exists( $key_or_property, $array_or_object ) ) {
				return $array_or_object;
			}
		}

		if ( isset( $keys[0] ) ) { // not used count() for performance reasons
			if ( $is_object ) {
				self::aku( $keys, $array_or_object->{$key_or_property} );
			} else {
				self::aku( $keys, $array_or_object[ $key_or_property ] );
			}
		} else {
			if ( $is_object ) {
				unset( $array_or_object->{$key_or_property} );
			} else {
				unset( $array_or_object[ $key_or_property ] );
			}
		}

		return $array_or_object;
	}
}

