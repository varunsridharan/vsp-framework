<?php

namespace VSP\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Class Price_Calculation
 *
 * @package VSP\Helper
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 0.8.5
 */
class Price_Calculation {
	/**
	 * Returns All Possible Price Types
	 *
	 * @static
	 * @return mixed|void
	 * @since 0.8.9.7
	 */
	public static function types() {
		return apply_filters( 'vsp/price_calculation/types', array(
			'fixed'      => esc_html__( 'Fixed', 'vsp-framework' ),
			'percentage' => esc_html__( 'Percentage (%)', 'vsp-framework' ),
		) );
	}

	/**
	 * Returns All Possible Price Operators
	 *
	 * @static
	 * @return mixed|void
	 * @since 0.8.9.7
	 */
	public static function operators() {
		return apply_filters( 'vsp/price_calculation/operators', array(
			'add' => esc_html__( 'Add (+)', 'vsp-framework' ),
			'sub' => esc_html__( 'Subtract (-)', 'vsp-framework' ),
		) );
	}

	/**
	 * Validates if given value is addition key.
	 *
	 * @param $type
	 *
	 * @static
	 * @return bool
	 * @since 0.8.9.7
	 */
	public static function is_operator_add( $type ) {
		return ( in_array( strtolower( $type ), array( 'add', '+' ), true ) );
	}

	/**
	 * Validates if given value is subraction key.
	 *
	 * @param $type
	 *
	 * @static
	 * @return bool
	 * @since 0.8.9.7
	 */
	public static function is_operator_sub( $type ) {
		return ( in_array( strtolower( $type ), array( 'sub', '-' ), true ) );
	}

	/**
	 * Handles Price Calculation.
	 *
	 * @param string|int $existing_price
	 * @param string|int $new_price
	 * @param string     $operator
	 *
	 * @return bool
	 */
	public static function fixed( $existing_price, $new_price, $operator ) {
		if ( self::is_operator_add( $operator ) ) {
			return $existing_price + $new_price;
		} elseif ( self::is_operator_sub( $operator ) ) {
			return $existing_price - $new_price;
		}
		return false;
	}

	/**
	 * Handles Percentage Calculation.
	 *
	 * @param string|int $existing_price
	 * @param string|int $new_price
	 * @param string     $operator
	 *
	 * @return bool
	 */
	public static function percentage( $existing_price, $new_price, $operator ) {
		$price = $new_price;
		if ( self::is_operator_add( $operator ) ) {
			$price = $existing_price + ( $existing_price * ( $new_price / 100 ) );
		} elseif ( self::is_operator_sub( $operator ) ) {
			$price = $existing_price - ( $existing_price * ( $new_price / 100 ) );
		}
		return (float) $price;
	}

	/**
	 * Handles Price Calcultion Concept.
	 *
	 * @param string|int  $existing_price
	 * @param string|int  $new_price
	 * @param string      $operator
	 * @param string      $rule
	 * @param bool|string $force_update
	 *
	 * @return string
	 */
	public static function get( $existing_price, $new_price, $operator, $rule, $force_update ) {
		if ( empty( $existing_price ) ) {
			if ( ! empty( $force_update ) && in_array( $force_update, array( true, 'yes', 'on', 1, '1' ), true ) ) {
				return $new_price;
			}
			return $existing_price;
		}

		$price = $new_price;
		switch ( $rule ) {
			case 'fixed':
				$price = static::fixed( $existing_price, $new_price, $operator );
				break;
			case 'percentage':
				$price = static::percentage( $existing_price, $new_price, $operator );
				break;
			default:
				$types = array_keys( self::types() );
				if ( in_array( $rule, $types, true ) ) {
					$price = apply_filters( 'vsp/price_calculation/' . $rule, $existing_price, $new_price, $operator );
				}
				break;
		}
		return wc_format_decimal( $price );
	}
}
