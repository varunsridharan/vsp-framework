<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */


if ( ! function_exists( 'vsp_ajax_url' ) ) {
	/**
	 * Returns Ajax URL.
	 *
	 * @param array $query_args
	 *
	 * @return string
	 */
	function vsp_ajax_url( $query_args = array() ) {
		$admin_url = admin_url( 'admin-ajax.php' );
		return add_query_arg( $query_args, $admin_url );
	}
}

if ( ! function_exists( 'vsp_send_json_callback' ) ) {
	/**
	 * Send Json Callback array in ajax.
	 * used for sweatalert / trigger custom js functions.
	 *
	 * @param bool  $status .
	 * @param array $functions .
	 * @param array $other_info .
	 * @param null  $status_code .
	 */
	function vsp_send_json_callback( $status = true, $functions = array(), $other_info = array(), $status_code = null ) {
		$function = ( true === $status ) ? 'wp_send_json_success' : 'wp_send_json_error';

		if ( is_string( $functions ) ) {
			$functions = array( $functions );
		}

		foreach ( $functions as $fid => $val ) {
			if ( is_numeric( $fid ) ) {
				unset( $functions[ $fid ] );
				$fid = 'VSPJS' . md5( wp_json_encode( $val ) ) . 'FUNCTION';
			}
			$functions[ $fid ] = trim( $val );
		}

		$data = array_merge( array( 'callback' => $functions ), $other_info );
		$function( $data, $status_code );
		wp_die();
	}
}


if ( ! function_exists( 'vsp_send_callback_error' ) ) {
	/**
	 * Sends JSON Callback as failure.
	 *
	 * @param array $functions
	 * @param array $data
	 * @param null  $status_code
	 */
	function vsp_send_callback_error( $functions = array(), $data = array(), $status_code = null ) {
		vsp_send_json_callback( false, $functions, $data, $status_code );
	}
}


if ( ! function_exists( 'vsp_send_callback_success' ) ) {
	/**
	 * Sends JSON Callback as success.
	 *
	 * @param array $functions
	 * @param array $data
	 * @param null  $status_code
	 */
	function vsp_send_callback_success( $functions = array(), $data = array(), $status_code = null ) {
		vsp_send_json_callback( true, $functions, $data, $status_code );
	}
}