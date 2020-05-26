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
	 * Handles Price Calculation.
	 *
	 * @param string|int $existing_price
	 * @param string|int $new_price
	 * @param string     $operator
	 *
	 * @return bool
	 */
	public static function fixed( $existing_price, $new_price, $operator ) {
		switch ( $operator ) {
			case 'add':
			case 'ADD':
			case '+':
				return $existing_price + $new_price;
				break;
			case 'sub':
			case 'SUB':
			case '-':
				return $existing_price - $new_price;
				break;
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
		switch ( $operator ) {
			case 'add':
			case 'ADD':
			case '+':
				$price = $existing_price + ( $existing_price * ( $new_price / 100 ) );
				break;
			case 'sub':
			case 'SUB':
			case '-':
				$price = $existing_price - ( $existing_price * ( $new_price / 100 ) );
				break;
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
		}
		return wc_format_decimal( $price );
	}
}
