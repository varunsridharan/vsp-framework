<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vsp_notices' ) ) {
	/**
	 * Creates a instance of a given notice class
	 *
	 * @param string $type .
	 *
	 * @return \VSP_WP_Admin_Notices|\VSP_WP_Notice
	 */
	function vsp_notices( $type = '' ) {
		if ( empty( $type ) ) {
			static $vsp_notices;

			if ( ! isset( $vsp_notices ) ) {
				$vsp_notices = VSP_WP_Admin_Notices::getInstance();
			}
			return $vsp_notices;
		}

		$_instance = new VSP_WP_Notice();

		switch ( $type ) {
			case 'error':
				$_instance->setType( VSP_WP_Notice::TYPE_ERROR );
				break;
			case 'update':
				$_instance->setType( VSP_WP_Notice::TYPE_UPDATED );
				break;
			case 'upgrade':
				$_instance->setType( VSP_WP_Notice::TYPE_UPDATED_NAG );
				break;
		}
		return $_instance;
	}
}

if ( ! function_exists( 'vsp_notice' ) ) {
	/**
	 * Updates Database With Given Notices Details
	 *
	 * @param string $message .
	 * @param string $type .
	 * @param array  $args .
	 */
	function vsp_notice( $message, $type = 'update', $args = array() ) {
		$defaults = array(
			'title'  => false,
			'times'  => 1,
			'screen' => array(),
			'users'  => array(),
		);
		$args     = wp_parse_args( $args, $defaults );

		if ( false !== $args['title'] && ( empty( $message ) || '' === $message ) ) {
			$message       = $args['title'];
			$args['title'] = false;
		}

		$method        = ( is_array( $args['users'] ) ) ? 'addUsers' : 'addUser';
		$sticky_method = ( true === $args['times'] ) ? 'setSticky' : 'setTimes';
		$_instance     = vsp_notices( $type );

		$_instance->setContent( $message );
		$_instance->setScreens( $args['screen'] );
		$_instance->$method( $args['users'] );
		$_instance->$sticky_method( $args['times'] );

		if ( false !== $args['title'] ) {
			$_instance->setTitle( $args['title'] );
		}

		vsp_notices()->addNotice( $_instance );
	}
}

if ( ! function_exists( 'vsp_notice_error' ) ) {
	/**
	 * Creates a error notice instances and saves in DB
	 *
	 * @param string $message .
	 * @param int    $times .
	 * @param array  $screen .
	 * @param array  $args .
	 */
	function vsp_notice_error( $title = false, $message, $times = 1, $screen = array(), $args = array() ) {
		$args['title']  = $title;
		$args['times']  = $times;
		$args['screen'] = $screen;
		if ( isset( $args['on_ajax'] ) && false === $args['on_ajax'] && vsp_is_ajax() ) {
			return;
		}
		vsp_notice( $message, 'error', $args );
	}
}

if ( ! function_exists( 'vsp_notice_update' ) ) {
	/**
	 * Creates a error update instances and saves in DB
	 *
	 * @param string $message .
	 * @param int    $times .
	 * @param array  $screen .
	 * @param array  $args .
	 */
	function vsp_notice_update( $title, $message, $times = 1, $screen = array(), $args = array() ) {
		$args['title']  = $title;
		$args['times']  = $times;
		$args['screen'] = $screen;
		if ( isset( $args['on_ajax'] ) && false === $args['on_ajax'] && vsp_is_ajax() ) {
			return;
		}
		vsp_notice( $message, 'update', $args );
	}
}

if ( ! function_exists( 'vsp_notice_upgrade' ) ) {
	/**
	 * Creates a error update instances and saves in DB
	 *
	 * @param string $message .
	 * @param int    $times .
	 * @param array  $screen .
	 * @param array  $args .
	 */
	function vsp_notice_upgrade( $title, $message, $times = 1, $screen = array(), $args = array() ) {
		$args['title']  = $title;
		$args['times']  = $times;
		$args['screen'] = $screen;
		if ( isset( $args['on_ajax'] ) && false === $args['on_ajax'] && vsp_is_ajax() ) {
			return;
		}
		vsp_notice( $message, 'upgrade', $args );
	}
}

if ( ! function_exists( 'vsp_js_alert' ) ) {
	/**
	 * Creats JS code to show SweatAlert
	 *
	 * @param string $title .
	 * @param string $text .
	 * @param string $type .
	 * @param array  $options .
	 * @param mixed  $queue .
	 *
	 * @return string
	 */
	function vsp_js_alert( $title = '', $text = '', $type = '', $options = array(), $queue = '' ) {
		$defaults    = array(
			'title' => $title,
			'text'  => $text,
			'type'  => $type,
		);
		$opts        = wp_parse_args( $options, $defaults );
		$opts        = array_filter( $opts );
		$after       = isset( $opts['after'] ) ? $opts['after'] : '';
		$return_html = '';
		$name        = 'swal' . rand( 1, 100 );
		$opts        = VSP_Localize_API::handle_js_function( $opts );

		$return_html .= VSP_Helper::php_to_js( $name, $opts, false );
		$return_html .= 'swal(vsp_js_function(' . $name . '))';

		if ( ! empty( $queue ) ) {
			$return_html .= '.queue(' . $queue . ')';
		}

		if ( ! empty( $after ) ) {
			$return_html .= '.then((value)=> {' . $after . '})';
		}

		$return_html .= ';';
		return $return_html;
	}
}

if ( ! function_exists( 'vsp_js_alert_success' ) ) {
	/**
	 * JS Sucess Alert
	 *
	 * @param string $title .
	 * @param string $text .
	 * @param array  $options .
	 *
	 * @return string
	 */
	function vsp_js_alert_success( $title = '', $text = '', $options = array() ) {
		return vsp_js_alert( $title, $text, 'success', $options );
	}
}

if ( ! function_exists( 'vsp_js_alert_error' ) ) {
	/**
	 * JS Error Alert
	 *
	 * @param string $title .
	 * @param string $text .
	 * @param array  $options .
	 *
	 * @return string
	 */
	function vsp_js_alert_error( $title = '', $text = '', $options = array() ) {
		return vsp_js_alert( $title, $text, 'error', $options );
	}
}

if ( ! function_exists( 'vsp_js_alert_warning' ) ) {
	/**
	 * JS Warning Alert
	 *
	 * @param string $title .
	 * @param string $text .
	 * @param array  $options .
	 *
	 * @return string
	 */
	function vsp_js_alert_warning( $title = '', $text = '', $options = array() ) {
		return vsp_js_alert( $title, $text, 'warning', $options );
	}
}

if ( ! function_exists( 'vsp_js_alert_info' ) ) {
	/**
	 * JS Info Alert
	 *
	 * @param string $title .
	 * @param string $text .
	 * @param array  $options .
	 *
	 * @return string
	 */
	function vsp_js_alert_info( $title = '', $text = '', $options = array() ) {
		return vsp_js_alert( $title, $text, 'info', $options );
	}
}
