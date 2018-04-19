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
 * @package   vsp-framework
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
		// strip ASCII chars 32 and under.
		$string = filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );
		// strip ASCII chars 127 and higher.
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
	 * Return a string with insane UTF-8 characters removed, like invisible
	 * characters, unused code points, and other weirdness. It should
	 * accept the common types of characters defined in Unicode.
	 *
	 * The following are allowed characters:
	 *
	 * p{L} - any kind of letter from any language
	 * p{Mn} - a character intended to be combined with another character without taking up extra space (e.g. accents, umlauts, etc.)
	 * p{Mc} - a character intended to be combined with another character that takes up extra space (vowel signs in many Eastern languages)
	 * p{Nd} - a digit zero through nine in any script except ideographic scripts
	 * p{Zs} - a whitespace character that is invisible, but does take up space
	 * p{P} - any kind of punctuation character
	 * p{Sm} - any mathematical symbol
	 * p{Sc} - any currency sign
	 *
	 * pattern definitions from http://www.regular-expressions.info/unicode.html
	 *
	 * @since 4.0.0
	 *
	 * @param string $string .
	 *
	 * @return mixed
	 */
	public static function str_to_sane_utf8( $string ) {
		$sane_string = preg_replace( '/[^\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Zs}\p{P}\p{Sm}\p{Sc}]/u', '', $string );
		// preg_replace with the /u modifier can return null or false on failure.
		return ( is_null( $sane_string ) || false === $sane_string ) ? $string : $sane_string;
	}

	/**
	 * Convert array into XML by recursively generating child elements
	 * First instantiate a new XML writer object:
	 * $xml = new XMLWriter();
	 * Open in memory (alternatively you can use a local URI for file output)
	 * $xml->openMemory();
	 * Then start the document
	 * $xml->startDocument( '1.0', 'UTF-8' );
	 * Don't forget to end the document and output the memory
	 * $xml->endDocument();
	 * $your_xml_string = $xml->outputMemory();
	 *
	 * @since 2.2.0
	 *
	 * @param \XMLWriter   $xml_writer XML writer instance.
	 * @param string|array $element_key name for element, e.g. <per_page>.
	 * @param string|array $element_value value for element, e.g. 100.
	 *
	 * @return string generated XML
	 */
	public static function array_to_xml( $xml_writer, $element_key, $element_value = array() ) {
		if ( is_array( $element_value ) ) {
			// handle attributes.
			if ( '@attributes' === $element_key ) {
				foreach ( $element_value as $attribute_key => $attribute_value ) {
					$xml_writer->startAttribute( $attribute_key );
					$xml_writer->text( $attribute_value );
					$xml_writer->endAttribute();
				}
				return;
			}
			// handle multi-elements (e.g. multiple <Order> elements).
			if ( is_numeric( key( $element_value ) ) ) {
				// recursively generate child elements.
				foreach ( $element_value as $child_element_key => $child_element_value ) {
					$xml_writer->startElement( $element_key );
					foreach ( $child_element_value as $sibling_element_key => $sibling_element_value ) {
						self::array_to_xml( $xml_writer, $sibling_element_key, $sibling_element_value );
					}
					$xml_writer->endElement();
				}
			} else {
				// start root element.
				$xml_writer->startElement( $element_key );
				// recursively generate child elements.
				foreach ( $element_value as $child_element_key => $child_element_value ) {
					self::array_to_xml( $xml_writer, $child_element_key, $child_element_value );
				}
				// end root element.
				$xml_writer->endElement();
			}
		} else {
			// handle single elements.
			if ( '@value' === $element_key ) {
				$xml_writer->text( $element_value );
			} else {
				// wrap element in CDATA tags if it contains illegal characters.
				if ( false !== strpos( $element_value, '<' ) || false !== strpos( $element_value, '>' ) ) {
					$xml_writer->startElement( $element_key );
					$xml_writer->writeCdata( $element_value );
					$xml_writer->endElement();
				} else {
					$xml_writer->writeElement( $element_key, $element_value );
				}
			}
			return;
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
	 * Convert a 2-character country code into its 3-character equivalent, or
	 * vice-versa, e.g.
	 * 1) given USA, returns US
	 * 2) given US, returns USA
	 *
	 * @since 4.2.0
	 *
	 * @param string $code ISO-3166-alpha-2 or ISO-3166-alpha-3 country code.
	 *
	 * @return string country code
	 */
	public static function convert_country_code( $code ) {
		// ISO 3166-alpha-2 => ISO 3166-alpha3.
		$countries = array(
			'AF' => 'AFG',
			'AL' => 'ALB',
			'DZ' => 'DZA',
			'AD' => 'AND',
			'AO' => 'AGO',
			'AG' => 'ATG',
			'AR' => 'ARG',
			'AM' => 'ARM',
			'AU' => 'AUS',
			'AT' => 'AUT',
			'AZ' => 'AZE',
			'BS' => 'BHS',
			'BH' => 'BHR',
			'BD' => 'BGD',
			'BB' => 'BRB',
			'BY' => 'BLR',
			'BE' => 'BEL',
			'BZ' => 'BLZ',
			'BJ' => 'BEN',
			'BT' => 'BTN',
			'BO' => 'BOL',
			'BA' => 'BIH',
			'BW' => 'BWA',
			'BR' => 'BRA',
			'BN' => 'BRN',
			'BG' => 'BGR',
			'BF' => 'BFA',
			'BI' => 'BDI',
			'KH' => 'KHM',
			'CM' => 'CMR',
			'CA' => 'CAN',
			'CV' => 'CPV',
			'CF' => 'CAF',
			'TD' => 'TCD',
			'CL' => 'CHL',
			'CN' => 'CHN',
			'CO' => 'COL',
			'KM' => 'COM',
			'CD' => 'COD',
			'CG' => 'COG',
			'CR' => 'CRI',
			'CI' => 'CIV',
			'HR' => 'HRV',
			'CU' => 'CUB',
			'CY' => 'CYP',
			'CZ' => 'CZE',
			'DK' => 'DNK',
			'DJ' => 'DJI',
			'DM' => 'DMA',
			'DO' => 'DOM',
			'EC' => 'ECU',
			'EG' => 'EGY',
			'SV' => 'SLV',
			'GQ' => 'GNQ',
			'ER' => 'ERI',
			'EE' => 'EST',
			'ET' => 'ETH',
			'FJ' => 'FJI',
			'FI' => 'FIN',
			'FR' => 'FRA',
			'GA' => 'GAB',
			'GM' => 'GMB',
			'GE' => 'GEO',
			'DE' => 'DEU',
			'GH' => 'GHA',
			'GR' => 'GRC',
			'GD' => 'GRD',
			'GT' => 'GTM',
			'GN' => 'GIN',
			'GW' => 'GNB',
			'GY' => 'GUY',
			'HT' => 'HTI',
			'HN' => 'HND',
			'HU' => 'HUN',
			'IS' => 'ISL',
			'IN' => 'IND',
			'ID' => 'IDN',
			'IR' => 'IRN',
			'IQ' => 'IRQ',
			'IE' => 'IRL',
			'IL' => 'ISR',
			'IT' => 'ITA',
			'JM' => 'JAM',
			'JP' => 'JPN',
			'JO' => 'JOR',
			'KZ' => 'KAZ',
			'KE' => 'KEN',
			'KI' => 'KIR',
			'KP' => 'PRK',
			'KR' => 'KOR',
			'KW' => 'KWT',
			'KG' => 'KGZ',
			'LA' => 'LAO',
			'LV' => 'LVA',
			'LB' => 'LBN',
			'LS' => 'LSO',
			'LR' => 'LBR',
			'LY' => 'LBY',
			'LI' => 'LIE',
			'LT' => 'LTU',
			'LU' => 'LUX',
			'MK' => 'MKD',
			'MG' => 'MDG',
			'MW' => 'MWI',
			'MY' => 'MYS',
			'MV' => 'MDV',
			'ML' => 'MLI',
			'MT' => 'MLT',
			'MH' => 'MHL',
			'MR' => 'MRT',
			'MU' => 'MUS',
			'MX' => 'MEX',
			'FM' => 'FSM',
			'MD' => 'MDA',
			'MC' => 'MCO',
			'MN' => 'MNG',
			'ME' => 'MNE',
			'MA' => 'MAR',
			'MZ' => 'MOZ',
			'MM' => 'MMR',
			'NA' => 'NAM',
			'NR' => 'NRU',
			'NP' => 'NPL',
			'NL' => 'NLD',
			'NZ' => 'NZL',
			'NI' => 'NIC',
			'NE' => 'NER',
			'NG' => 'NGA',
			'NO' => 'NOR',
			'OM' => 'OMN',
			'PK' => 'PAK',
			'PW' => 'PLW',
			'PA' => 'PAN',
			'PG' => 'PNG',
			'PY' => 'PRY',
			'PE' => 'PER',
			'PH' => 'PHL',
			'PL' => 'POL',
			'PT' => 'PRT',
			'QA' => 'QAT',
			'RO' => 'ROU',
			'RU' => 'RUS',
			'RW' => 'RWA',
			'KN' => 'KNA',
			'LC' => 'LCA',
			'VC' => 'VCT',
			'WS' => 'WSM',
			'SM' => 'SMR',
			'ST' => 'STP',
			'SA' => 'SAU',
			'SN' => 'SEN',
			'RS' => 'SRB',
			'SC' => 'SYC',
			'SL' => 'SLE',
			'SG' => 'SGP',
			'SK' => 'SVK',
			'SI' => 'SVN',
			'SB' => 'SLB',
			'SO' => 'SOM',
			'ZA' => 'ZAF',
			'ES' => 'ESP',
			'LK' => 'LKA',
			'SD' => 'SDN',
			'SR' => 'SUR',
			'SZ' => 'SWZ',
			'SE' => 'SWE',
			'CH' => 'CHE',
			'SY' => 'SYR',
			'TJ' => 'TJK',
			'TZ' => 'TZA',
			'TH' => 'THA',
			'TL' => 'TLS',
			'TG' => 'TGO',
			'TO' => 'TON',
			'TT' => 'TTO',
			'TN' => 'TUN',
			'TR' => 'TUR',
			'TM' => 'TKM',
			'TV' => 'TUV',
			'UG' => 'UGA',
			'UA' => 'UKR',
			'AE' => 'ARE',
			'GB' => 'GBR',
			'US' => 'USA',
			'UY' => 'URY',
			'UZ' => 'UZB',
			'VU' => 'VUT',
			'VA' => 'VAT',
			'VE' => 'VEN',
			'VN' => 'VNM',
			'YE' => 'YEM',
			'ZM' => 'ZMB',
			'ZW' => 'ZWE',
			'TW' => 'TWN',
			'CX' => 'CXR',
			'CC' => 'CCK',
			'HM' => 'HMD',
			'NF' => 'NFK',
			'NC' => 'NCL',
			'PF' => 'PYF',
			'YT' => 'MYT',
			'GP' => 'GLP',
			'PM' => 'SPM',
			'WF' => 'WLF',
			'TF' => 'ATF',
			'BV' => 'BVT',
			'CK' => 'COK',
			'NU' => 'NIU',
			'TK' => 'TKL',
			'GG' => 'GGY',
			'IM' => 'IMN',
			'JE' => 'JEY',
			'AI' => 'AIA',
			'BM' => 'BMU',
			'IO' => 'IOT',
			'VG' => 'VGB',
			'KY' => 'CYM',
			'FK' => 'FLK',
			'GI' => 'GIB',
			'MS' => 'MSR',
			'PN' => 'PCN',
			'SH' => 'SHN',
			'GS' => 'SGS',
			'TC' => 'TCA',
			'MP' => 'MNP',
			'PR' => 'PRI',
			'AS' => 'ASM',
			'UM' => 'UMI',
			'GU' => 'GUM',
			'VI' => 'VIR',
			'HK' => 'HKG',
			'MO' => 'MAC',
			'FO' => 'FRO',
			'GL' => 'GRL',
			'GF' => 'GUF',
			'MQ' => 'MTQ',
			'RE' => 'REU',
			'AX' => 'ALA',
			'AW' => 'ABW',
			'AN' => 'ANT',
			'SJ' => 'SJM',
			'AC' => 'ASC',
			'TA' => 'TAA',
			'AQ' => 'ATA',
			'CW' => 'CUW',
		);
		if ( 3 === strlen( $code ) ) {
			$countries = array_flip( $countries );
		}
		return isset( $countries[ $code ] ) ? $countries[ $code ] : $code;
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
