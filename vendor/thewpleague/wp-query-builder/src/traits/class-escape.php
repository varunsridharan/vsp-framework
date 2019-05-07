<?php
/**
 * The Query Builder
 *
 * @package TheLeague\Database
 */

namespace TheLeague\Database\Traits;

/**
 * Escape class.
 */
trait Escape {

	/**
	 * Escape array values for sql
	 *
	 * @param  array $arr Array to escape.
	 *
	 * @return array
	 */
	public function esc_array( $arr ) {
		return array_map( array( $this, 'esc_value' ), $arr );
	}

	/**
	 * Escape value for sql
	 *
	 * @param  mixed $value Value to escape.
	 *
	 * @return mixed
	 */
	public function esc_value( $value ) {
		global $wpdb;

		if ( is_int( $value ) ) {
			return $wpdb->prepare( '%d', $value );
		}

		if ( is_float( $value ) ) {
			return $wpdb->prepare( '%f', $value );
		}

		if ( is_string( $value ) ) {
			return 'null' === $value ? $value : $wpdb->prepare( '%s', $value );
		}

		return $value;
	}

	/**
	 * Escape value for like statement
	 *
	 * @param string $value  Value for like statement.
	 * @param string $start  (Optional) The start of like query.
	 * @param string $end    (Optional) The end of like query.
	 *
	 * @return string
	 */
	public function esc_like( $value, $start = '%', $end = '%' ) {
		global $wpdb;
		return $start . $wpdb->esc_like( $value ) . $end;
	}
}
