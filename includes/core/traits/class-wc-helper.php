<?php

namespace VSP\Core\Traits;

use WC_Payment_Gateway;
use WC_Payment_Gateways;
use WPOnion\Exception\Cache_Not_Found;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Trait WC_Helper
 *
 * @package VSP\Core\Traits
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait WC_Helper {
	/**
	 * Fetches Gateways From WooCommerce And Returns It.
	 *
	 * @param bool $slug
	 *
	 * @static
	 * @return array|mixed
	 */
	public static function wc_payment_methods( $slug = false ) {
		try {
			return ( $slug ) ? vsp_get_cache( 'vsp/wc/payment_methods/slugs' ) : vsp_get_cache( 'vsp/wc/payment_methods/all' );
		} catch ( Cache_Not_Found $exception ) {
			$methods  = WC_Payment_Gateways::instance()->payment_gateways;
			$gateways = array();
			$slugs    = array();
			/*  @var WC_Payment_Gateway $method */
			foreach ( $methods as $method ) {
				if ( 'yes' === $method->enabled ) {
					if ( 'Mollie' === $method->title && $method->has_mollie() ) {
						$instance = $GLOBALS['wc_mollie'];
						$gateway  = $instance->get_gateway();
						$methods  = $gateway->get_methods();
						foreach ( $methods as $method ) {
							$method->id           = 'mollie_' . $method->id;
							$method->description  = $method->description . ' (Mollie)';
							$gateways[]           = $method;
							$slugs[ $method->id ] = $method->title;
						}
					} else {
						$gateways[]           = $method;
						$slugs[ $method->id ] = $method->title;
					}
				}
			}
			vsp_set_cache( 'vsp/wc/paymet_methods/slugs', $slugs );
			vsp_set_cache( 'vsp/wc/paymet_methods/all', $gateways );
			return ( $slug ) ? $slugs : $gateways;
		}
	}

	/**
	 * @param bool $slug
	 *
	 * @static
	 * @return mixed
	 * @throws \Exception
	 */
	public static function wc_shipping_methods( $slug = false ) {
		try {
			return ( $slug ) ? vsp_get_cache( 'vsp/wc/shipping_methods/slugs' ) : vsp_get_cache( 'vsp/wc/shipping_methods/all' );
		} catch ( Cache_Not_Found $exception ) {
			$slugs = array();
			if ( sizeof( WC()->shipping->shipping_methods ) > 0 ) {
				$shipping_methods = \WC_Shipping::instance()->shipping_methods;
			} else {
				$shipping_methods = \WC_Shipping::instance()
					->load_shipping_methods();
			}

			foreach ( $shipping_methods as $method ) {
				$slugs[ $method->id ] = $method->method_title;
			}
			vsp_set_cache( 'vsp/wc/shipping_methods/slugs', $slugs );
			vsp_set_cache( 'vsp/wc/shipping_methods/all', $shipping_methods );
			return ( $slug ) ? $slugs : $shipping_methods;
		}
	}

	/**
	 * Fetches & Returns Product's SKU.
	 *
	 * @param $product_id
	 *
	 * @static
	 * @return mixed
	 */
	public static function wc_product_sky_by_id( $product_id ) {
		return get_post_meta( $product_id, '_sku', true );
	}

	/**
	 * Checks if product exists in cart.
	 *
	 * @param $product_id
	 *
	 * @static
	 * @return bool
	 */
	public static function wc_has_product_in_cart( $product_id ) {
		foreach ( wc()->cart->get_cart() as $key => $val ) {
			$_product = $val['data'];

			if ( $product_id === $_product->get_id() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Clears WC Cart.
	 *
	 * @static
	 */
	public static function wc_clear_cart() {
		if ( function_exists( 'wc' ) ) {
			wc()->cart->empty_cart( true );
		}
	}

	/**
	 * Clears Cart if Not Empty.
	 *
	 * @static
	 */
	public static function wc_clear_cart_if_notempty() {
		if ( function_exists( 'wc' ) ) {
			if ( ! empty( wc()->cart->get_cart() ) ) {
				static::wc_clear_cart();
			}
		}
	}
}

