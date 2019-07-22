<?php

namespace VSP;

use WPOnion\Exception\Cache_Not_Found;

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
		use Core\Traits\WP;
		use Core\Traits\WC_Helper;
		use Core\Traits\Array_Helper;
		use Core\Traits\URL;
		use Core\Traits\String_Helper;

		/**
		 * Gets the key value from globals.
		 *
		 * @param string $key
		 *
		 * @return string|boolean|bool
		 * @since 1.1.4
		 *
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
		 * @return bool
		 * @since 2.2.0
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
			try {
				$translation = vsp_get_cache( 'vsp/human_time' );
			} catch ( Cache_Not_Found $exception ) {
				$translation = array(
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
				vsp_set_cache( 'vsp/humna_time', $translation );
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
				return $number_of_units . ' ' . $translation[ $translation_key . $key ];
			}
			return false;
		}
	}
}
