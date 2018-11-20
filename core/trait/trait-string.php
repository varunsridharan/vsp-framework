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

/**
 * Trait VSP_String_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait VSP_String_Trait {
	/**
	 * MB_ENCODING
	 *
	 * @var string
	 */
	protected static $MB_ENCODING = 'UTF-8';

	/**
	 * Returns true if the haystack string starts with needle
	 *
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public static function str_starts_with( $haystack, $needle ) {
		if ( static::multibyte_loaded() ) {
			if ( '' === $needle ) {
				return true;
			}
			return 0 === mb_strpos( $haystack, $needle, 0, self::$MB_ENCODING );
		} else {
			$needle = self::str_to_ascii( $needle );
			if ( '' === $needle ) {
				return true;
			}
			return 0 === strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
		}
	}

	/**
	 * Returns a string with all non-ASCII characters removed. This is useful
	 * for any string functions that expect only ASCII chars and can't
	 * safely handle UTF-8. Note this only allows ASCII chars in the range
	 * 33-126 (newlines/carriage returns are stripped)
	 *
	 * @since 2.2.0
	 *
	 * @param string $string string to make ASCII
	 *
	 * @return string
	 */
	public static function str_to_ascii( $string ) {
		// strip ASCII chars 32 and under
		$string = filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );
		// strip ASCII chars 127 and higher
		return filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH );
	}

	/**
	 * Return true if the haystack string ends with needle
	 *
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public static function str_ends_with( $haystack, $needle ) {
		if ( '' === $needle ) {
			return true;
		}
		if ( self::multibyte_loaded() ) {
			return mb_substr( $haystack, -mb_strlen( $needle, self::$MB_ENCODING ), null, self::$MB_ENCODING ) === $needle;
		} else {
			$haystack = self::str_to_ascii( $haystack );
			$needle   = self::str_to_ascii( $needle );
			return substr( $haystack, -strlen( $needle ) ) === $needle;
		}
	}

	/**
	 * Returns true if the needle exists in haystack
	 *
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public static function str_exists( $haystack, $needle ) {
		if ( self::multibyte_loaded() ) {
			if ( '' === $needle ) {
				return false;
			}
			return false !== mb_strpos( $haystack, $needle, 0, self::$MB_ENCODING );
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
	 * @param string $string text to truncate
	 * @param int    $length total desired length of string, including omission
	 * @param string $omission omission text, defaults to '...'
	 *
	 * @return string
	 */
	public static function str_truncate( $string, $length, $omission = '...' ) {
		if ( self::multibyte_loaded() ) {
			// bail if string doesn't need to be truncated
			if ( mb_strlen( $string, self::$MB_ENCODING ) <= $length ) {
				return $string;
			}
			$length -= mb_strlen( $omission, self::$MB_ENCODING );
			return mb_substr( $string, 0, $length, self::$MB_ENCODING ) . $omission;
		} else {
			$string = self::str_to_ascii( $string );
			// bail if string doesn't need to be truncated
			if ( strlen( $string ) <= $length ) {
				return $string;
			}
			$length -= strlen( $omission );
			return substr( $string, 0, $length ) . $omission;
		}
	}

	/**
	 * Converts Simple String Into Boolean
	 *
	 * @param $str
	 *
	 * @return bool
	 * @static
	 */
	public static function to_bool( $str ) {
		$return = false;
		switch ( strtolower( $str ) ) {
			case 'on':
			case '1':
			case 'yes':
			case 'true':
			case true:
				$return = true;
				break;
			case 'off':
			case '0':
			case 'no':
			case 'false':
			case false:
			case null:
				$return = false;
				break;
		}
		return $return;
	}

	/**
	 * Converts Numeric Value into Human Readable View
	 *
	 * @example 1024B => 1KB | 1024KB => 1MB | 1024MB => 1GB
	 *
	 * @param     $bytes
	 * @param int $precision
	 *
	 * @return string
	 * @static
	 */
	public static function to_human_bytes( $bytes, $precision = 2 ) {
		$kilobyte = 1024;
		$megabyte = $kilobyte * 1024;
		$gigabyte = $megabyte * 1024;
		$terabyte = $gigabyte * 1024;

		if ( ( $bytes >= 0 ) && ( $bytes < $kilobyte ) ) {
			return $bytes . ' B';

		} elseif ( ( $bytes >= $kilobyte ) && ( $bytes < $megabyte ) ) {
			return round( $bytes / $kilobyte, $precision ) . ' KB';

		} elseif ( ( $bytes >= $megabyte ) && ( $bytes < $gigabyte ) ) {
			return round( $bytes / $megabyte, $precision ) . ' MB';

		} elseif ( ( $bytes >= $gigabyte ) && ( $bytes < $terabyte ) ) {
			return round( $bytes / $gigabyte, $precision ) . ' GB';

		} elseif ( $bytes >= $terabyte ) {
			return round( $bytes / $terabyte, $precision ) . ' TB';
		} else {
			return $bytes . ' B';
		}
	}
}
