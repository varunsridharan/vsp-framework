<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'vsp_ajax_action' ) ) {
	/**
	 * Check if current request has action parameter and returns it
	 *
	 * @return bool
	 */
	function vsp_ajax_action() {
		return ( vsp_is_request( 'ajax' ) && isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : false;
	}
}

if ( ! function_exists( 'vsp_is_heartbeat' ) ) {
	/**
	 * Checks if current request is heartbeat
	 *
	 * @return bool
	 */
	function vsp_is_heartbeat() {
		return ( vsp_is_ajax() === true && vsp_is_ajax( 'heartbeat' ) === true ) ? true : false;
	}
}

if ( ! function_exists( 'vsp_is_ajax' ) ) {
	/**
	 * Checks if current request is ajax
	 * Also takes required action key to check if the ajax is exactly the action is passed
	 *
	 * @param string $action .
	 *
	 * @return bool
	 */
	function vsp_is_ajax( $action = '' ) {
		if ( empty( $action ) ) {
			return vsp_is_request( 'ajax' );
		}

		return ( vsp_ajax_action() !== false && vsp_ajax_action() === $action ) ? true : false;
	}
}

if ( ! function_exists( 'vsp_is_cron' ) ) {
	/**
	 * Checks if current request is cron
	 *
	 * @return bool
	 */
	function vsp_is_cron() {
		return vsp_is_request( 'cron' );
	}
}

if ( ! function_exists( 'vsp_is_admin' ) ) {
	/**
	 * Checks if current request is admin
	 *
	 * @return bool
	 */
	function vsp_is_admin() {
		return vsp_is_request( 'admin' );
	}
}

if ( ! function_exists( 'vsp_is_frontend' ) ) {
	/**
	 * Checks if current request is frontend
	 *
	 * @return bool
	 */
	function vsp_is_frontend() {
		return vsp_is_request( 'frontend' );
	}
}

if ( ! function_exists( 'vsp_is_request' ) ) {
	/**
	 * Checks What kind of request is it.
	 *
	 * @param string $type .
	 *
	 * @return bool
	 */
	function vsp_is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
		return false;
	}
}
