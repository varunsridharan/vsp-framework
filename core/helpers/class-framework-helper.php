<?php
/**
 * VSP Framework Helper
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 31-01-2018
 * Time: 06:47 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/helpers
 * @copyright GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VSP_Framework_Helper
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class VSP_Framework_Helper {
	/**
	 * Encoding used for mb_*() string function
	 */
	const MB_ENCODING = 'UTF-8';

	/**
	 * Returns true if the haystack string starts with needle
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 *
	 * @param string $haystack .
	 * @param string $needle .
	 *
	 * @return bool
	 */
	public static function str_starts_with( $haystack, $needle ) {
		if ( self::multibyte_loaded() ) {
			if ( '' === $needle ) {
				return true;
			}
			return 0 === mb_strpos( $haystack, $needle, 0, self::MB_ENCODING );
		} else {
			$needle = self::str_to_ascii( $needle );
			if ( '' === $needle ) {
				return true;
			}
			return 0 === strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
		}
	}

	/**
	 * Helper method to check if the multibyte extension is loaded, which
	 * indicates it's safe to use the mb_*() string methods
	 *
	 * @since 2.2.0
	 * @return bool
	 */
	protected static function multibyte_loaded() {
		return extension_loaded( 'mbstring' );
	}

	/**
	 * Returns a string with all non-ASCII characters removed. This is useful
	 * for any string functions that expect only ASCII chars and can't
	 * safely handle UTF-8. Note this only allows ASCII chars in the range
	 * 33-126 (newlines/carriage returns are stripped)
	 *
	 * @since 2.2.0
	 *
	 * @param string $string string to make ASCII.
	 *
	 * @return string
	 */
	public static function str_to_ascii( $string ) {
		$string = filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );
		return filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH );
	}

	/**
	 * Return true if the haystack string ends with needle
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 *
	 * @param string $haystack .
	 * @param string $needle .
	 *
	 * @return bool
	 */
	public static function str_ends_with( $haystack, $needle ) {
		if ( '' === $needle ) {
			return true;
		}
		if ( self::multibyte_loaded() ) {
			return mb_substr( $haystack, -mb_strlen( $needle, self::MB_ENCODING ), null, self::MB_ENCODING ) === $needle;
		} else {
			$haystack = self::str_to_ascii( $haystack );
			$needle   = self::str_to_ascii( $needle );
			return substr( $haystack, -strlen( $needle ) ) === $needle;
		}
	}

	/**
	 * Returns true if the needle exists in haystack
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 *
	 * @param string $haystack .
	 * @param string $needle .
	 *
	 * @return bool
	 */
	public static function str_exists( $haystack, $needle ) {
		if ( self::multibyte_loaded() ) {
			if ( '' === $needle ) {
				return false;
			}
			return false !== mb_strpos( $haystack, $needle, 0, self::MB_ENCODING );
		} else {
			$needle = self::str_to_ascii( $needle );
			if ( '' === $needle ) {
				return false;
			}
			return false !== strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
		}
	}

	/**
	 * Truncates a given $string after a given $length if string is longer than
	 * $length. The last characters will be replaced with the $omission string
	 * for a total length not exceeding $length
	 *
	 * @since 2.2.0
	 *
	 * @param string $string text to truncate .
	 * @param int    $length total desired length of string, including omission .
	 * @param string $omission omission text, defaults to '...' .
	 *
	 * @return string
	 */
	public static function str_truncate( $string, $length, $omission = '...' ) {
		if ( self::multibyte_loaded() ) {
			// bail if string doesn't need to be truncated.
			if ( mb_strlen( $string, self::MB_ENCODING ) <= $length ) {
				return $string;
			}
			$length -= mb_strlen( $omission, self::MB_ENCODING );
			return mb_substr( $string, 0, $length, self::MB_ENCODING ) . $omission;
		} else {
			$string = self::str_to_ascii( $string );
			// bail if string doesn't need to be truncated.
			if ( strlen( $string ) <= $length ) {
				return $string;
			}
			$length -= strlen( $omission );
			return substr( $string, 0, $length ) . $omission;
		}
	}

	/**
	 * Format a number with 2 decimal points, using a period for the decimal
	 * separator and no thousands separator.
	 * Commonly used for payment gateways which require amounts in this format.
	 *
	 * @since 3.0.0
	 *
	 * @param float $number .
	 *
	 * @return string
	 */
	public static function number_format( $number ) {
		return number_format( (float) $number, 2, '.', '' );
	}

	/**
	 * Triggers a PHP error.
	 *
	 * This wrapper method ensures AJAX isn't broken in the process.
	 *
	 * @since 4.6.0
	 *
	 * @param string $message the error message.
	 * @param int    $type Optional. The error type. Defaults to E_USER_NOTICE.
	 */
	public static function trigger_error( $message, $type = E_USER_NOTICE ) {
		if ( is_callable( 'is_ajax' ) && is_ajax() ) {
			switch ( $type ) {
				case E_USER_NOTICE:
					$prefix = 'Notice: ';
					break;
				case E_USER_WARNING:
					$prefix = 'Warning: ';
					break;
				default:
					$prefix = '';
			}
			error_log( $prefix . $message );
		} else {
			trigger_error( $message, $type );
		}
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

	/**
	 * Safely get and trim data from $_POST
	 *
	 * @since 3.0.0
	 *
	 * @param string $key array key to get from $_POST array.
	 *
	 * @return string value from $_POST or blank string if $_POST[ $key ] is not set
	 */
	public static function get_post( $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			return trim( $_POST[ $key ] );
		}
		return '';
	}

	/**
	 * Safely get and trim data from $_REQUEST
	 *
	 * @since 3.0.0
	 *
	 * @param string $key array key to get from $_REQUEST array.
	 *
	 * @return string value from $_REQUEST or blank string if $_REQUEST[ $key ] is not set
	 */
	public static function get_request( $key ) {
		if ( isset( $_REQUEST[ $key ] ) ) {
			return trim( $_REQUEST[ $key ] );
		}
		return '';
	}
}
