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

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Helper' ) ) {
	/**
	 * Class VSP_Helper
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	final class Helper {
		use Core\Traits\JS;
		use Core\Traits\WP;
		use Core\Traits\Array_Helper;
		use Core\Traits\Validate;
		use Core\Traits\IP;
		use Core\Traits\URL;
		use Core\Traits\File;
		use Core\Traits\MimeType;
		use Core\Traits\String_Helper;

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
		public static function human_time( $seconds ) {
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