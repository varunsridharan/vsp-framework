<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'vsp_send_json_callback' ) ) {
	/**
	 * Send Json Callback array in ajax.
	 * used for sweatalert / trigger custom js functions.
	 *
	 * @param bool         $status
	 * @param string|array $functions
	 * @param array        $other_info
	 * @param array        $window_args
	 * @param string       $status_code
	 */
	function vsp_send_json_callback( $status = true, $functions = array(), $other_info = array(), $window_args = array(), $status_code = null ) {
		$function = ( true === $status ) ? 'wp_send_json_success' : 'wp_send_json_error';

		if ( is_object( $functions ) ) {
			$functions = array( $functions );
		} elseif ( is_string( $functions ) ) {
			$functions = array( $functions );
		}

		if ( ! empty( $window_args ) ) {
			foreach ( $window_args as $key => $args ) {
				if ( is_array( $args ) ) {
					$window_args[ $key ] = 'window.vsp_js_helper.window_arg(' . wp_json_encode( $args ) . ')';
				}
			}
			$functions = array_merge( $functions, $window_args );
		}

		foreach ( $functions as $fid => $val ) {
			if ( is_numeric( $fid ) ) {
				unset( $functions[ $fid ] );
				$fid = 'vspjs' . md5( wp_json_encode( $val ) . '-' . microtime() ) . 'function';
			}
			$functions[ $fid ] = trim( $val );
			$functions[ $fid ] = str_replace( array( "\n", "\r" ), ' ', $functions[ $fid ] );
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
	 * @param string|array $functions
	 * @param mixed        $data
	 * @param array        $window_args
	 * @param string       $status_code
	 */
	function vsp_send_callback_error( $functions = array(), $data = array(), $window_args = array(), $status_code = null ) {
		vsp_send_json_callback( false, $functions, $data, $window_args, $status_code );
	}
}

if ( ! function_exists( 'vsp_send_callback_success' ) ) {
	/**
	 * Sends JSON Callback as success.
	 *
	 * @param string|array $functions
	 * @param mixed        $data
	 * @param array        $window_args
	 * @param string       $status_code
	 */
	function vsp_send_callback_success( $functions = array(), $data = array(), $window_args = array(), $status_code = null ) {
		vsp_send_json_callback( true, $functions, $data, $window_args, $status_code );
	}
}
