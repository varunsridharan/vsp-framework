<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 31-01-2018
 * Time: 06:52 AM
 */

if ( ! defined( "ABSPATH" ) ) {
	exit;
}

/**
 * Class VSP_WC_Helper
 */
class VSP_WC_Helper {

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
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @since 3.0.0
	 * @return string woocommerce version number or null
	 */
	protected static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
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
	 * Determines if the installed version of WooCommerce is less than 3.0.
	 *
	 * @since 4.6.0
	 * @return bool
	 */
	public static function is_wc_version_lt_3_0() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '3.0', '<' );
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