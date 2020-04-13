<?php

namespace VSP\Core\Traits\WC_Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! trait_exists( '\VSP\Core\Traits\WC_Compatibility\Version' ) ) {
	/**
	 * Trait Version
	 *
	 * @package VSP\Core\Traits
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 */
	trait Version {
		/**
		 * Helper method to get the version of the currently installed WooCommerce
		 *
		 * @return string woocommerce version number or null
		 * @since 3.0.0
		 */
		public static function version() {
			return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
		}

		/**
		 * Determines if the installed version of WooCommerce is 3.0 or greater.
		 *
		 * @return bool
		 * @since 4.6.0
		 */
		public static function is_version_gte_3_0() {
			return static::version() && version_compare( static::version(), '3.0', '>=' );
		}

		/**
		 * Determines if the installed version of WooCommerce is less than 3.0.
		 *
		 * @return bool
		 * @since 4.6.0
		 */
		public static function is_version_lt_3_0() {
			return static::version() && version_compare( static::version(), '3.0', '<' );
		}

		/**
		 * Determines if the installed version of WooCommerce is 3.1 or greater.
		 *
		 * @return bool
		 * @since 4.6.5
		 */
		public static function is_version_gte_3_1() {
			return static::version() && version_compare( static::version(), '3.1', '>=' );
		}

		/**
		 * Determines if the installed version of WooCommerce is less than 3.1.
		 *
		 * @return bool
		 * @since 4.6.5
		 */
		public static function is_version_lt_3_1() {
			return static::version() && version_compare( static::version(), '3.1', '<' );
		}

		/**
		 * Determines if the installed version of WooCommerce meets or exceeds the
		 * passed version.
		 *
		 * @param string $version version number to compare
		 *
		 * @return bool
		 * @since 4.7.3
		 *
		 */
		public static function is_version_gte( $version ) {
			return static::version() && version_compare( static::version(), $version, '>=' );
		}

		/**
		 * Determines if the installed version of WooCommerce is lower than the
		 * passed version.
		 *
		 * @param string $version version number to compare
		 *
		 * @return bool
		 * @since 4.7.3
		 *
		 */
		public static function is_version_lt( $version ) {
			return static::version() && version_compare( static::version(), $version, '<' );
		}

		/**
		 * Returns true if the installed version of WooCommerce is greater than $version
		 *
		 * @param string $version the version to compare
		 *
		 * @return boolean true if the installed version of WooCommerce is > $version
		 * @since 2.0.0
		 */
		public static function is_version_gt( $version ) {
			return static::version() && version_compare( static::version(), $version, '>' );
		}
	}
}
