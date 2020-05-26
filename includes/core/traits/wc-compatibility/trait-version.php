<?php

namespace VSP\Core\Traits\WC_Compatibility;

defined( 'ABSPATH' ) || exit;

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
	 */
	public static function version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * @param int|string $version
	 * @param string     $compare
	 *
	 * @return bool
	 */
	public static function compare( $version, $compare = '>=' ) {
		return static::version() && version_compare( static::version(), $version, $compare );
	}
}
