<?php

namespace VSP\Core\Traits\WC_Compatibility;


if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! trait_exists( '\VSP\Core\Traits\WC_Compatibility\Product' ) ) {
	/**
	 * Trait Product
	 *
	 * @package VSP\Core\Traits\WC_Compatibility
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 */
	trait Product {
		/**
		 * Checks & Returns Proper Product ID.
		 *
		 * @param $product
		 *
		 * @static
		 * @return int|mixed
		 */
		public static function get_product_id( $product ) {
			if ( is_numeric( $product ) ) {
				return $product;
			}

			if ( $product instanceof \WC_Product && method_exists( $product, 'get_id' ) ) {
				return $product->get_id();
			}

			if ( static::is_version_lt_3_0() && $product instanceof \WC_Product && isset( $product->ID ) ) {
				return $product->ID;
			}
			return false;
		}
	}
}
