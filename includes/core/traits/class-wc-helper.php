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
}

