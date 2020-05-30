<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

/**
 * Class Helper
 *
 * @package VSP
 * @author Varun Sridharan <varunsridharan23@gmail.com>
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
	 */
	public static function multibyte_loaded() {
		return extension_loaded( 'mbstring' );
	}

	/**
	 * Generates A Rand MD5 String and returns it.
	 *
	 * @return string
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
		$tokens      = array(
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
			$key             = ( 1 !== $number_of_units ) ? 's' : '';
			return $number_of_units . ' ' . $translation[ $translation_key . $key ];
		}
		return false;
	}

	/**
	 * Converts VSP Error To WC Notice.
	 *
	 * @param \VSP\Error $vsp_error
	 *
	 * @since 0.8.7
	 */
	public static function vsp_error_to_wc_notice( $vsp_error ) {
		if ( ! empty( $vsp_error->get_error_codes() ) ) {
			foreach ( $vsp_error->get_error_codes() as $code ) {
				wc_add_notice( $vsp_error->get_error_message( $code ), 'error', $vsp_error->get_error_data( $code ) );
			}
		}
	}
}
