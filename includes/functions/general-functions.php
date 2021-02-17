<?php

defined( 'ABSPATH' ) || exit;

use VSP\Modules\Logger;
use VSP\Modules\Logger\File_Handler;

if ( ! function_exists( 'vsp_current_screen' ) ) {
	/**
	 * Returns Either Full Screen Object or just the screen id.
	 *
	 * @param bool $only_id .
	 *
	 * @return bool|string|\WP_Screen
	 */
	function vsp_current_screen( $only_id = true ) {
		$screen = get_current_screen();
		if ( false === $only_id ) {
			return $screen;
		}
		return isset( $screen->id ) ? $screen->id : false;
	}
}

if ( ! function_exists( 'vsp_is_screen' ) ) {
	/**
	 * Checks provided Screen ID if its current screen.
	 *
	 * @param string $check_screen .
	 * @param string $current_screen .
	 *
	 * @return bool
	 */
	function vsp_is_screen( $check_screen = '', $current_screen = '' ) {
		if ( ! empty( $check_screen ) ) {
			$current_screen = ( empty( $current_screen ) ) ? vsp_current_screen( true ) : $current_screen;

			if ( is_array( $check_screen ) ) {
				return in_array( $current_screen, $check_screen, true );
			}
			return ( $check_screen === $current_screen );
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_get_time_in_seconds' ) ) {
	/**
	 * Returns Cache Time in numeric values
	 *
	 * @param string $time .
	 *
	 * @return float|int
	 */
	function vsp_get_time_in_seconds( $time ) {
		$times = explode( '_', $time );
		if ( ! is_array( $times ) ) {
			return $time;
		}

		$time_limit = $times[0];
		$type       = $times[1];
		$time_limit = intval( $time_limit );

		switch ( $type ) {
			case 'seconds':
			case 'second':
			case 'sec':
				$time = $time_limit;
				break;

			case 'minute':
			case 'minutes':
			case 'min':
				$time = $time_limit * MINUTE_IN_SECONDS;
				break;

			case 'hour':
			case 'hours':
			case 'hrs':
				$time = $time_limit * HOUR_IN_SECONDS;
				break;

			case 'days':
			case 'day':
				$time = $time_limit * DAY_IN_SECONDS;
				break;

			case 'weeks':
			case 'week':
				$time = $time_limit * WEEK_IN_SECONDS;
				break;

			case 'month':
			case 'months':
				$time = $time_limit * MONTH_IN_SECONDS;
				break;

			case 'year':
			case 'years':
				$time = $time_limit * YEAR_IN_SECONDS;
				break;
		}

		return intval( $time );
	}
}

if ( ! function_exists( 'vsp_placeholder_img' ) ) {
	/**
	 * Returns VSP Placeholder Image
	 *
	 * @return string
	 */
	function vsp_placeholder_img() {
		return apply_filters( 'vsp/placeholder_img', vsp_url( 'assets/img/noimage.png' ) );
	}
}

if ( ! function_exists( 'vsp_set_time_limit' ) ) {
	/**
	 * Wrapper for set_time_limit to see if it is enabled.
	 *
	 * @param int $limit Time limit.
	 */
	function vsp_set_time_limit( $limit = 0 ) {
		// @codingStandardsIgnoreStart
		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( $limit );
		}
		// @codingStandardsIgnoreEnd
	}
}

if ( ! function_exists( 'vsp_doing_it_wrong' ) ) {
	/**
	 * Wrapper for vsp_doing_it_wrong.
	 *
	 * @param string $function Function used.
	 * @param string $message Message to log.
	 * @param string $version Version the message was added in.
	 */
	function vsp_doing_it_wrong( $function, $message, $version ) {
		// @codingStandardsIgnoreStart
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
		if ( is_ajax() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			vsp_log_msg( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// @codingStandardsIgnoreEnd
	}
}

if ( ! function_exists( 'vsp_get_logger' ) ) {
	/**
	 * Use the vsp_logging_class filter to change the logging class.
	 * You may provide one of the following:
	 *     - a class name which will be instantiated as `new $class` with no arguments
	 *     - an instance which will be used directly as the logger
	 * In either case, the class or instance *must* implement WC_Logger_Interface.
	 *
	 * @param bool $subpath
	 * @param bool $file_name
	 * @param bool $filesize
	 *
	 * @return \VSP\Modules\Logger
	 *
	 */
	function vsp_get_logger( $subpath = false, $file_name = null, $filesize = false ) {
		$class      = apply_filters( 'vsp/log/class', '\VSP\Modules\Logger' );
		$implements = class_implements( $class );
		if ( is_array( $implements ) && in_array( 'VSP\Core\Interfaces\Logger', $implements, true ) ) {
			$logger = ( is_object( $class ) ) ? $class : new $class( array( new File_Handler( $subpath, $file_name, $filesize ) ) );
		} else {
			/* translators: 1: class name 2: woocommerce_logging_class 3: WC_Logger_Interface */
			$smgs = sprintf( esc_html__( 'The class %1$s provided by %2$s filter must implement %3$s.', 'vsp-framework' ), '<code>' . esc_html( is_object( $class ) ? get_class( $class ) : $class ) . '</code>', '<code>vsp_logging_class</code>', '<code>\VSP\Core\Interfaces\Logger</code>' );
			vsp_doing_it_wrong( __FUNCTION__, $smgs, '3.0' );
			$logger = new Logger( array( new File_Handler( $subpath, $file_name, $filesize ) ) );
		}
		return $logger;
	}
}

if ( ! function_exists( 'vsp_logger' ) ) {
	/**
	 * Returns An Valid Instance Of Logger To Handle Logs From VSP Framework.
	 *
	 * @return \VSP\Modules\Logger
	 */
	function vsp_logger() {
		static $logger = null;
		if ( null === $logger ) {
			$logger = vsp_get_logger( false, null, false );
		}
		return $logger;
	}
}

if ( ! function_exists( 'vsp_log_msg' ) ) {
	/**
	 * Logs Give message to a given handler
	 *
	 * @param string|array $messages
	 * @param string       $type
	 * @param bool         $handler
	 * @param array        $context
	 *
	 * @return bool
	 */
	function vsp_log_msg( $messages = '', $type = 'critical', $handler = false, $context = array() ) {
		$types = array( 'critical', 'emergency', 'alert', 'error', 'warning', 'notice', 'info', 'debug' );

		if ( ! in_array( $type, $types, true ) ) {
			return false;
		}

		if ( is_array( $messages ) ) {
			$messages = implode( PHP_EOL, $messages );
		}

		$messages .= PHP_EOL;

		if ( false === $handler ) {
			$handler = vsp_logger();
		}

		if ( $handler instanceof Logger && method_exists( $handler, $type ) ) {
			$handler->$type( $messages, $context );
			return true;
		} elseif ( vsp_logger() instanceof Logger && method_exists( vsp_logger(), $type ) ) {
			$msg     = array_merge( array( esc_html__( 'Tried To Log A Message But Failed Got unknown Handler', 'vsp-framework' ) ), wp_debug_backtrace_summary( null, 0, false ) );
			$content = <<<TEXT

//////////////////////// = VSP Critical = /////////////////////////////////
${msg}
${messages}
/////////////////////////////////////////////////////////////////

TEXT;

			vsp_log_msg( $content, 'notice', vsp_logger() );
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_date_format' ) ) {
	/**
	 * Returns Site's Date Format From Database
	 *
	 * @return mixed
	 */
	function vsp_date_format() {
		return apply_filters( 'vsp/date/format', get_option( 'date_format' ) );
	}
}

if ( ! function_exists( 'vsp_time_format' ) ) {
	/**
	 * Returns Site's Time Format From Database
	 *
	 * @return mixed
	 */
	function vsp_time_format() {
		return apply_filters( 'vsp/time/format', get_option( 'time_format' ) );
	}
}

if ( ! function_exists( 'vsp_json_last_error' ) ) {
	/**
	 * Checks if last json had any errors.
	 *
	 * @return string|null
	 */
	function vsp_json_last_error() {
		switch ( function_exists( 'json_last_error' ) ? json_last_error() : -1 ) {
			case JSON_ERROR_NONE:
				return null;
				break;
			case JSON_ERROR_DEPTH:
				return esc_html__( 'Maximum stack depth exceeded', 'vsp-framework' );
				break;
			case JSON_ERROR_STATE_MISMATCH:
				return esc_html__( 'Underflow or the modes mismatch', 'vsp-framework' );
				break;
			case JSON_ERROR_CTRL_CHAR:
				return esc_html__( 'Unexpected control character found', 'vsp-framework' );
				break;
			case JSON_ERROR_SYNTAX:
				return esc_html__( 'Syntax error, malformed JSON', 'vsp-framework' );
				break;
			case JSON_ERROR_UTF8:
				return esc_html__( 'Malformed UTF-8 characters, possibly incorrectly encoded', 'vsp-framework' );
				break;
			default:
				return esc_html__( 'Unknown error', 'vsp-framework' );
				break;
		}
	}
}

if ( ! function_exists( 'vsp_is_json' ) ) {
	/**
	 * Checks If Given String is JSON.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	function vsp_is_json( $string = '' ) {
		if ( is_string( $string ) ) {
			json_decode( $string );
			return ( json_last_error() === JSON_ERROR_NONE );
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_is_error' ) ) {
	/**
	 * Checks if given instance is a \VSP_Error Instance.
	 *
	 * @param $thing
	 *
	 * @return bool
	 */
	function vsp_is_error( $thing ) {
		return ( $thing instanceof \VSP\Error );
	}
}
