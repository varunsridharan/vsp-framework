<?php
/**
 * The Query Builder
 *
 * @package TheLeague\Database
 */

namespace TheLeague\Database\Traits;

/**
 * Translate class.
 */
trait Translate {

	/**
	 * Translate the current query to an SQL select statement
	 *
	 * @return string
	 */
	private function translateSelect() { // @codingStandardsIgnoreLine
		$build = array( 'select' );

		if ( $this->found_rows ) {
			$build[] = 'SQL_CALC_FOUND_ROWS';
		}
		if ( $this->distinct ) {
			$build[] = 'distinct';
		}

		// Build the selected fields.
		$build[] = ! empty( $this->statements['select'] ) && is_array( $this->statements['select'] ) ? join( ', ', $this->statements['select'] ) : '*';

		// Append the table.
		$build[] = 'from ' . $this->table;

		// Build the where statements.
		if ( ! empty( $this->statements['wheres'] ) ) {
			$build[] = join( ' ', $this->statements['wheres'] );
		}

		// Build the group by statements.
		if ( ! empty( $this->statements['groups'] ) ) {
			$build[] = 'group by ' . join( ', ', $this->statements['groups'] );

			if ( ! empty( $this->statements['having'] ) ) {
				$build[] = $this->statements['having'];
			}
		}

		// Build the order statement.
		if ( ! empty( $this->statements['orders'] ) ) {
			$build[] = $this->translateOrderBy();
		}

		// Build offset and limit.
		if ( ! empty( $this->limit ) ) {
			$build[] = $this->limit;
		}

		return join( ' ', $build );
	}

	/**
	 * Translate the current query to an SQL update statement
	 *
	 * @return string
	 */
	private function translateUpdate() { // @codingStandardsIgnoreLine
		$build = array( "update {$this->table} set" );

		// Add the values.
		$values = array();
		foreach ( $this->statements['values'] as $key => $value ) {
			$values[] = $key . ' = ' . $this->esc_value( $value );
		}

		if ( ! empty( $values ) ) {
			$build[] = join( ', ', $values );
		}

		// Build the where statements.
		if ( ! empty( $this->statements['wheres'] ) ) {
			$build[] = join( ' ', $this->statements['wheres'] );
		}

		// Build offset and limit.
		if ( ! empty( $this->limit ) ) {
			$build[] = $this->limit;
		}

		return join( ' ', $build );
	}

	/**
	 * Translate the current query to an SQL delete statement
	 *
	 * @return string
	 */
	private function translateDelete() { // @codingStandardsIgnoreLine
		$build = array( "delete from {$this->table}" );

		// Build the where statements.
		if ( ! empty( $this->statements['wheres'] ) ) {
			$build[] = join( ' ', $this->statements['wheres'] );
		}

		// Build offset and limit.
		if ( ! empty( $this->limit ) ) {
			$build[] = $this->limit;
		}

		return join( ' ', $build );
	}

	/**
	 * Build the order by statement
	 *
	 * @return string
	 */
	protected function translateOrderBy() { // @codingStandardsIgnoreLine
		$build = array();

		foreach ( $this->statements['orders'] as $column => $direction ) {

			// in case a raw value is given we had to
			// put the column / raw value an direction inside another
			// array because we cannot make objects to array keys.
			if ( is_array( $direction ) ) {
				list( $column, $direction ) = $direction;
			}

			if ( ! is_null( $direction ) ) {
				$column .= ' ' . $direction;
			}

			$build[] = $column;
		}
		return 'order by ' . join( ', ', $build );
	}
}
