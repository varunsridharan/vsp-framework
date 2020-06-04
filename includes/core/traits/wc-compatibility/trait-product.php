<?php

namespace VSP\Core\Traits\WC_Compatibility;

defined( 'ABSPATH' ) || exit;

use WC_Product;

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
	 * @return int|mixed
	 */
	public static function get_product_id( $product ) {
		if ( is_numeric( $product ) ) {
			return $product;
		}

		if ( $product instanceof WC_Product && method_exists( $product, 'get_id' ) ) {
			return $product->get_id();
		}

		if ( static::compare( '3.0', '<' ) && $product instanceof WC_Product && isset( $product->ID ) ) {
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
	 */
	public static function get_product_type( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		return ( static::compare( '3.0', '>=' ) ) ? $product->get_type() : $product->product_type;
	}
}
