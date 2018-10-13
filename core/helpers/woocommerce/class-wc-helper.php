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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VSP_WC_Helper
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class VSP_WC_Helper {
	/**
	 * Gets the statuses that are considered "paid".
	 *
	 * @since 5.1.0
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
	 * Determines if the installed version of WooCommerce is 3.0 or greater.
	 *
	 * @since 4.6.0
	 * @return bool
	 */
	public static function is_wc_version_gte_3_0() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.0', '>=' );
	}

	/**
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @since 3.0.0
	 * @return string woocommerce version number or null
	 */
	public static function get_wc_version() {

		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Formats a date for output.
	 *
	 * Backports WC 3.0.0's wc_format_datetime() to older versions.
	 *
	 * @since  4.6.0
	 *
	 * @param \WC_DateTime|\SV_WC_DateTime $date date object
	 * @param string                       $format date format
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

			if ( ! is_a( $date, 'VSP_Date_Time' ) ) { // TODO: verify this {CW 2017-07-18}
				return '';
			}

			return $date->date_i18n( $format );
		}
	}

	/**
	 * Normalizes a WooCommerce page screen ID.
	 *
	 * Needed because WordPress uses a menu title (which is translatable), not slug, to generate screen ID.
	 * See details in: https://core.trac.wordpress.org/ticket/21454
	 * TODO: Add WP version check when https://core.trac.wordpress.org/ticket/18857 is addressed {BR 2016-12-12}
	 *
	 * @since 4.6.0
	 *
	 * @param string $slug slug for the screen ID to normalize (minus `woocommerce_page_`)
	 *
	 * @return string normalized screen ID
	 */
	public static function normalize_wc_screen_id( $slug = 'wc-settings' ) {

		// The textdomain usage is intentional here, we need to match the menu title.
		$prefix = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

		return $prefix . '_page_' . $slug;
	}

	/**
	 * Determines if the installed version of WooCommerce is less than 3.0.
	 *
	 * @since 4.6.0
	 * @return bool
	 */
	public static function is_wc_version_lt_3_0() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.0', '<' );
	}


	/**
	 * Determines if the installed version of WooCommerce is 3.1 or greater.
	 *
	 * @since 4.6.5
	 * @return bool
	 */
	public static function is_wc_version_gte_3_1() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.1', '>=' );
	}


	/**
	 * Determines if the installed version of WooCommerce is less than 3.1.
	 *
	 * @since 4.6.5
	 * @return bool
	 */
	public static function is_wc_version_lt_3_1() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.1', '<' );
	}


	/**
	 * Determines if the installed version of WooCommerce meets or exceeds the
	 * passed version.
	 *
	 * @since 4.7.3
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
	 * @since 4.7.3
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
	 * @since 2.0.0
	 *
	 * @param string $version the version to compare
	 *
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
	}

}
