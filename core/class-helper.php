<?php
/**
 * VSP Helper.
 *
 * Created by PhpStorm.
 * User: varun
 * Date : 13-10-2018
 * Time : 01:48 PM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core
 * @copyright GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'VSP_Helper' ) ) {
	/**
	 * Class VSP_Helper
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	final class VSP_Helper {
		use VSP_JS_Trait;
		use VSP_WP_Trait;
		use VSP_Array_Trait;
		use VSP_Validate_Trait;
		use VSP_IP_Trait;
		use VSP_Url_Trait;
		use VSP_File_Trait;
		use VSP_MimeType_Trait;
		use VSP_String_Trait;

		/**
		 * load_time
		 *
		 * @var array
		 */
		protected static $load_time = array();

		/**
		 * human_time_translations
		 *
		 * @var null
		 */
		protected static $human_time_translations = null;

		/**
		 * Gets the key value from globals.
		 *
		 * @since 1.1.4
		 *
		 * @param string $key
		 *
		 * @return string|boolean|bool
		 */
		protected static function global_vars( $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				return $_SERVER[ $key ];
			}
			if ( isset( $_ENV[ $key ] ) ) {
				return $_ENV[ $key ];
			}
			return false;
		}

		/**
		 * Calculate load time of pages or scripts.
		 *
		 * @param      $key
		 * @param bool $is_end
		 *
		 * @return bool|float
		 * @static
		 */
		public static function load_time( $key, $is_end = false ) {
			if ( false === $is_end ) {
				self::$load_time[ $key ] = microtime( true );
				$time                    = self::$load_time[ $key ];
			} else {
				if ( isset( self::$load_time[ $key ] ) ) {
					$time = round( microtime( true ) - self::$load_time, 4 );
					unset( self::$load_time[ $key ] );
				}
			}
			return isset( $time ) ? $time : false;
		}

		/**
		 * Helper method to check if the multibyte extension is loaded, which
		 * indicates it's safe to use the mb_*() string methods
		 *
		 * @since 2.2.0
		 * @return bool
		 */
		public static function multibyte_loaded() {
			return extension_loaded( 'mbstring' );
		}

		/**
		 * Convert a 2-character country code into its 3-character equivalent, or
		 * vice-versa, e.g.
		 *
		 * 1) given USA, returns US
		 * 2) given US, returns USA
		 *
		 * @since 4.2.0
		 *
		 * @param string $code ISO-3166-alpha-2 or ISO-3166-alpha-3 country code
		 *
		 * @return string country code
		 */
		public static function convert_country_code( $code ) {
			// ISO 3166-alpha-2 => ISO 3166-alpha3
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
		 * Generates A Rand MD5 String and returns it.
		 *
		 * @return string
		 * @static
		 */
		public static function rand_md5() {
			return md5( time() . '-' . uniqid( rand(), true ) . '-' . mt_rand( 1, 1000 ) );
		}

		/**
		 * Convert number of seconds to 'X {units}'
		 *
		 * E.g. 123 => '2 minutes'
		 * then you can use this string how you want, for e.g. append ' ago' => '2 minutes ago'
		 *
		 * @param int $seconds
		 *
		 * @return string
		 */
		function human_time( $seconds ) {
			if ( null === self::$human_time_translations ) {
				self::$human_time_translations = array(
					'year'    => __( 'year' ),
					'years'   => __( 'years' ),
					'month'   => __( 'month' ),
					'months'  => __( 'months' ),
					'week'    => __( 'week' ),
					'weeks'   => __( 'weeks' ),
					'day'     => __( 'day' ),
					'days'    => __( 'days' ),
					'hour'    => __( 'hour' ),
					'hours'   => __( 'hours' ),
					'minute'  => __( 'minute' ),
					'minutes' => __( 'minutes' ),
					'second'  => __( 'second' ),
					'seconds' => __( 'seconds' ),
				);
			}

			$tokens = array(
				31536000 => 'year',
				2592000  => 'month',
				604800   => 'week',
				86400    => 'day',
				3600     => 'hour',
				60       => 'minute',
				1        => 'second',
			);

			foreach ( $tokens as $unit => $translation_key ) {
				if ( $seconds < $unit ) {
					continue;
				}

				$number_of_units = floor( $seconds / $unit );

				$key = ( 1 !== $number_of_units ) ? 's' : '';
				return $number_of_units . ' ' . self::$human_time_translations[ $translation_key . $key ];
			}
			return false;
		}
	}
}
