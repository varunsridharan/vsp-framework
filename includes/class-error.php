<?php

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

use WP_Error;

/**
 * Class Error
 *
 * @package VSP
 * @author Varun Sridharan <varunsridharan23@gmail.com>
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
	 * @param string|int $code Error code.
	 * @param string     $msg Error message.
	 * @param mixed      $arg Optional. Error data.
	 *
	 * @return $this
	 */
	public function add( $code, $msg, $arg = '' ) {
		$msg = ( ! is_array( $msg ) ) ? array( $msg ) : $msg;
		foreach ( $msg as $m ) {
			parent::add( $code, $m, $arg );
		}
		return $this;
	}
}
