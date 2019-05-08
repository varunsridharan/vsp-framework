<?php
/**
 *
 * Project : wcisms
 * Date : 13-10-2018
 * Time : 06:39 AM
 * File : class-error.php
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @package wcisms
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

namespace VSP;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\VSP\Error' ) ) {
	/**
	 * Class Error
	 *
	 * @package VSP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Error extends WP_Error {
		/**
		 * Checks And Returns If This class has errors stored.
		 *
		 * @return bool
		 */
		public function has() {
			return ( ! empty( $this->errors ) );
		}

		/**
		 * Add an error or append additional message to an existing error.
		 *
		 * @since 2.1.0
		 *
		 * @param string|int $code Error code.
		 * @param string     $message Error message.
		 * @param mixed      $data Optional. Error data.
		 *
		 * @return $this|self
		 */
		public function add( $code, $message, $data = '' ) {
			if ( ! is_array( $message ) ) {
				$message = array( $message );
			}
			foreach ( $message as $m ) {
				parent::add( $code, $m, $data );
			}
			return $this;
		}

		/**
		 * Add data for error code.
		 *
		 * The error code can only contain one error data.
		 *
		 * @since 2.1.0
		 *
		 * @param mixed      $data Error data.
		 * @param string|int $code Error code.
		 *
		 * @return $this
		 */
		public function add_data( $data, $code = '' ) {
			parent::add_data( $data, $code );
			return $this;
		}

		/**
		 * Removes the specified error.
		 *
		 * This function removes all error messages associated with the specified
		 * error code, along with any error data for that code.
		 *
		 * @since 4.1.0
		 *
		 * @param string|int $code Error code.
		 *
		 * @return $this
		 */
		public function remove( $code ) {
			parent::remove( $code );
			return $this;
		}
	}
}
