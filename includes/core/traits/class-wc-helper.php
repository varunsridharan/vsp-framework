<?php

namespace VSP\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

use Exception;
use WC_Payment_Gateway;
use WC_Payment_Gateways;
use WC_Shipping;
use WC_Shipping_Zones;
use WPOnion\Exception\Cache_Not_Found;

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
	 * @param bool   $slug
	 * @param string $cache_key Custom Cache key for customized data via filter.
	 *
	 * @static
	 * @return array|mixed
	 */
	public static function wc_payment_methods( $slug = false, $cache_key = 'vsp' ) {
		try {
			return ( $slug ) ? vsp_get_cache( $cache_key . '/wc/payment_methods/slugs' ) : vsp_get_cache( $cache_key . '/wc/payment_methods/all' );
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
							$slugs[ $method->id ] = apply_filters( 'vsp_wc_payment_gateway_label', $method->title, $method, $cache_key );
						}
					} else {
						$gateways[]           = $method;
						$slugs[ $method->id ] = apply_filters( 'vsp_wc_payment_gateway_label', $method->title, $method, $cache_key );
					}
				}
			}
			vsp_set_cache( $cache_key . '/wc/paymet_methods/slugs', $slugs );
			vsp_set_cache( $cache_key . '/wc/paymet_methods/all', $gateways );
			return ( $slug ) ? $slugs : $gateways;
		}
	}

	/**
	 * @param bool   $slug
	 * @param string $cache_key Custom Cache key for customized data via filter.
	 *
	 * @static
	 * @return mixed
	 * @throws \Exception
	 */
	public static function wc_shipping_methods( $slug = false, $cache_key = 'vsp' ) {
		try {
			return ( $slug ) ? vsp_get_cache( $cache_key . '/wc/shipping_methods/slugs' ) : vsp_get_cache( $cache_key . '/wc/shipping_methods/all' );
		} catch ( Cache_Not_Found $exception ) {
			$slugs      = array();
			$is_smv     = ( ! empty( WC()->shipping->shipping_methods ) && count( WC()->shipping->shipping_methods ) > 0 );
			$s_instance = WC_Shipping::instance();

			try {
				$shipping_methods = ( $is_smv ) ? $s_instance->shipping_methods : $s_instance->load_shipping_methods();
			} catch ( Exception $exception ) {
				$shipping_methods = array();
			}

			if ( ! empty( $shipping_methods ) ) {
				foreach ( $shipping_methods as $method ) {
					$slugs[ $method->id ] = apply_filters( 'vsp_wc_shipping_methods_label', $method->method_title, $method );
				}

				vsp_set_cache( $cache_key . '/wc/shipping_methods/slugs', $slugs );
				vsp_set_cache( $cache_key . '/wc/shipping_methods/all', $shipping_methods );
			}
			return ( $slug ) ? $slugs : $shipping_methods;
		}
	}

	/**
	 * Fetches All Shipping Methods By Instance ID.
	 *
	 * @param string $cache_key Custom Cache key for customized data via filter.
	 *
	 * @static
	 * @return array
	 */
	public static function wc_shipping_methods_by_instance( $cache_key = 'vsp' ) {
		try {
			return vsp_get_cache( $cache_key . '/wc/shipping_methods_instance' );
		} catch ( Cache_Not_Found $exception ) {
			/**
			 * @var \WC_Shipping_Zone   $zone
			 * @var \WC_Shipping_Method $shipping_method
			 */
			try {
				$delivery_zones = WC_Shipping_Zones::get_zones();
			} catch ( Exception $exception_inner ) {
				$delivery_zones = array();
			}
			$return = array();
			if ( ! empty( $delivery_zones ) ) {
				foreach ( $delivery_zones as $zone ) {
					if ( ! empty( $zone['shipping_methods'] ) ) {
						$store = array();
						foreach ( $zone['shipping_methods'] as $shipping_method ) {
							$store[ $shipping_method->get_instance_id() ] = apply_filters( 'vsp_wc_shipping_methods_by_instance_label', $shipping_method->get_title(), $shipping_method );
						}
						$return[ $zone['zone_name'] ] = $store;
					}
				}
			}
			$exception->set( $return );
			return $return;
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
	 * @return bool
	 */
	public static function wc_clear_cart() {
		if ( function_exists( 'wc' ) ) {
			wc()->cart->empty_cart( true );
			return true;
		}
		return false;
	}

	/**
	 * Clears Cart if Not Empty.
	 *
	 * @static
	 * @return bool
	 */
	public static function wc_clear_cart_if_notempty() {
		return ( function_exists( 'wc' ) && ! empty( wc()->cart->get_cart() ) ) ? static::wc_clear_cart() : false;
	}
}

