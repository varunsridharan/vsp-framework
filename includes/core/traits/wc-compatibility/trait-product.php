<?php

namespace VSP\Core\Traits\WC_Compatibility;


use WC_Product;

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

			if ( $product instanceof WC_Product && method_exists( $product, 'get_id' ) ) {
				return $product->get_id();
			}

			if ( static::is_version_lt_3_0() && $product instanceof WC_Product && isset( $product->ID ) ) {
				return $product->ID;
			}
			return false;
		}

		/**
		 * Returns Product Type.
		 *
		 * @param \WC_Product|int $product
		 *
		 * @return mixed|string
		 * @static
		 */
		public static function get_product_type( $product ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}

			if ( ! $product instanceof WC_Product ) {
				return false;
			}

			return ( static::is_version_gte_3_0() ) ? $product->get_type() : $product->product_type;
		}
	}
}
