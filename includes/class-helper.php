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
		use Core\Traits\String_Helper;


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
					'year'    => __( 'year', 'vsp-framework' ),
					'years'   => __( 'years', 'vsp-framework' ),
					'month'   => __( 'month', 'vsp-framework' ),
					'months'  => __( 'months', 'vsp-framework' ),
					'week'    => __( 'week', 'vsp-framework' ),
					'weeks'   => __( 'weeks', 'vsp-framework' ),
					'day'     => __( 'day', 'vsp-framework' ),
					'days'    => __( 'days', 'vsp-framework' ),
					'hour'    => __( 'hour', 'vsp-framework' ),
					'hours'   => __( 'hours', 'vsp-framework' ),
					'minute'  => __( 'minute', 'vsp-framework' ),
					'minutes' => __( 'minutes', 'vsp-framework' ),
					'second'  => __( 'second', 'vsp-framework' ),
					'seconds' => __( 'seconds', 'vsp-framework' ),
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
