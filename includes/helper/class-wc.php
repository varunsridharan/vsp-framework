<?php
/**
 * Common Functions for WooCommerce Plugin
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 31-01-2018
 * Time: 06:52 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/helpers/woocommerce
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */


namespace VSP\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * Class VSP_WC_Helper
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class WC {
	/**
	 * Gets the statuses that are considered "paid".
	 *
	 * @return array
	 */
	public static function wc_get_is_paid_statuses() {
		if ( self::is_wc_version_gte_3_0() ) {
			return wc_get_is_paid_statuses();
		} else {
			return (array) apply_filters( 'woocommerce_order_is_paid_statuses', array( 'processing', 'completed' ) );
		}
	}

	/**
	 * Logs a doing_it_wrong message.
	 *
	 * Backports wc_doing_it_wrong() to WC 2.6.
	 *
	 * @param string $function function used
	 * @param string $message message to log
	 * @param string $version version the message was added in
	 *
	 */
	public static function wc_doing_it_wrong( $function, $message, $version ) {
		if ( self::is_wc_version_gte( '3.0' ) ) {
			wc_doing_it_wrong( $function, $message, $version );
		} else {
			$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
			if ( is_ajax() ) {
				do_action( 'doing_it_wrong_run', $function, $message, $version );
				error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
			} else {
				_doing_it_wrong( $function, $message, $version );
			}
		}
	}

	/**
	 * Formats a date for output.
	 *
	 * Backports WC 3.0.0's wc_format_datetime() to older versions.
	 *
	 * @param \WC_DateTime|\VSP\Helper\Date_Time $date date object
	 * @param string                             $format date format
	 *
	 * @return string
	 */
	public static function wc_format_datetime( $date, $format = '' ) {
		if ( self::is_wc_version_gte_3_0() ) {
			return wc_format_datetime( $date, $format );
		} else {
			if ( ! $format ) {
				$format = wc_date_format();
			}
			if ( ! is_a( $date, '\\SkyVerge\\WooCommerce\\PluginFramework\\v5_4_0\\SV_WC_DateTime' ) ) { // TODO: verify this {CW 2017-07-18}
				return '';
			}
			return $date->date_i18n( $format );
		}
	}

	/**
	 * Logs a deprecated function notice.
	 *
	 * @param string $function deprecated function name
	 * @param string $version deprecated-since version
	 * @param string $replacement replacement function name
	 *
	 * @since  5.0.0
	 *
	 */
	public static function wc_deprecated_function( $function, $version, $replacement = null ) {
		if ( self::is_wc_version_gte_3_0() ) {
			wc_deprecated_function( $function, $version, $replacement );
		} else {
			if ( is_ajax() ) {
				do_action( 'deprecated_function_run', $function, $replacement, $version );
				$log_string = 'The ' . $function . ' function is deprecated since version ' . $version . '.';
				$log_string .= $replacement ? " Replace with {$replacement}." : '';
				error_log( $log_string );
			} else {
				_deprecated_function( $function, $version, $replacement );
			}
		}
	}

	/**
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @return string woocommerce version number or null
	 */
	public static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Determines if the installed version of WooCommerce is 3.0 or greater.
	 *
	 * @return bool
	 */
	public static function is_wc_version_gte_3_0() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.0', '>=' );
	}

	/**
	 * Determines if the installed version of WooCommerce is less than 3.0.
	 *
	 * @return bool
	 */
	public static function is_wc_version_lt_3_0() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.0', '<' );
	}

	/**
	 * Determines if the installed version of WooCommerce is 3.1 or greater.
	 *
	 * @return bool
	 */
	public static function is_wc_version_gte_3_1() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.1', '>=' );
	}

	/**
	 * Determines if the installed version of WooCommerce is less than 3.1.
	 *
	 * @return bool
	 */
	public static function is_wc_version_lt_3_1() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.1', '<' );
	}

	/**
	 * Determines if the installed version of WooCommerce meets or exceeds the
	 * passed version.
	 *
	 * @param string $version version number to compare
	 *
	 * @return bool
	 */
	public static function is_wc_version_gte( $version ) {
		return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
	}

	/**
	 * Determines if the installed version of WooCommerce is lower than the
	 * passed version.
	 *
	 * @param string $version version number to compare
	 *
	 * @return bool
	 */
	public static function is_wc_version_lt( $version ) {
		return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version
	 *
	 * @param string $version the version to compare
	 *
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
	}
}
