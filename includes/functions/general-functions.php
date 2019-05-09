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

if ( ! function_exists( 'vsp_current_page_url' ) ) {
	/**
	 * Returns Current Page URL
	 *
	 * @return string
	 */
	function vsp_current_page_url() {
		$page_url = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) ? 'https' : 'http';
		$port     = ( isset( $_SERVER['SERVER_PORT'] ) && '80' !== $_SERVER['SERVER_PORT'] ) ? ':' . $_SERVER['SERVER_PORT'] : '';
		return $page_url . '://' . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
	}
}

if ( ! function_exists( 'vsp_get_time_in_seconds' ) ) {
	/**
	 * Returns Cache Time in numeric values
	 *
	 * @param string $time .
	 *
	 * @return float|int
	 * @example vsp_get_time_in_seconds('1_minute') => 60
	 *
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
	 * @return mixed
	 */
	function vsp_placeholder_img() {
		return apply_filters( 'vsp_placeholder_img', vsp_img( 'noimage.png' ) );
	}
}

if ( ! function_exists( 'vsp_validate_css_unit' ) ) {
	/**
	 * Validates CSS Units.
	 *
	 * @param string $value .
	 *
	 * @return string
	 */
	function vsp_validate_css_unit( $value ) {
		$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
		// allowed metrics: http://www.w3schools.com/cssref/css_units.asp.
		preg_match( $pattern, $value, $matches );
		$value = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
		$unit  = isset( $matches[2] ) ? $matches[2] : 'px';
		return $value . $unit;
	}
}

if ( ! function_exists( 'vsp_set_time_limit' ) ) {
	/**
	 * Wrapper for set_time_limit to see if it is enabled.
	 *
	 * @param int $limit Time limit.
	 */
	function vsp_set_time_limit( $limit = 0 ) {
		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( $limit );
		}
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
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// @codingStandardsIgnoreEnd
	}
}

if ( ! function_exists( 'vsp_get_logger' ) ) {
	/**
	 * Get a shared logger instance.
	 *
	 * Use the vsp_logging_class filter to change the logging class. You may provide one of the following:
	 *     - a class name which will be instantiated as `new $class` with no arguments
	 *     - an instance which will be used directly as the logger
	 * In either case, the class or instance *must* implement WC_Logger_Interface.
	 *
	 * @param bool $subpath
	 * @param bool $file_name
	 * @param bool $filesize
	 *
	 * @return mixed|\VSP\Modules\Logger
	 * @see VSP_Logger_Interface
	 *
	 */
	function vsp_get_logger( $subpath = false, $file_name = null, $filesize = false ) {
		$class      = apply_filters( 'vsp_logging_class', '\VSP\Modules\Logger' );
		$implements = class_implements( $class );
		if ( is_array( $implements ) && in_array( 'VSP\Core\Interfaces\Logger', $implements, true ) ) {
			$logger = ( is_object( $class ) ) ? $class : new $class( array( new \VSP\Modules\Logger\File_Handler( $subpath, $file_name, $filesize ) ) );
		} else {
			/* translators: 1: class name 2: woocommerce_logging_class 3: WC_Logger_Interface */
			$smgs = sprintf( __( 'The class %1$s provided by %2$s filter must implement %3$s.', 'vsp-framework' ), '<code>' . esc_html( is_object( $class ) ? get_class( $class ) : $class ) . '</code>', '<code>vsp_logging_class</code>', '<code>\VSP\Core\Interfaces\Logger</code>' );
			vsp_doing_it_wrong( __FUNCTION__, $smgs, '3.0' );
			$logger = new \VSP\Modules\Logger( array( new  \VSP\Modules\Logger\File_Handler( $subpath, $file_name, $filesize ) ) );
		}
		return $logger;
	}
}

if ( ! function_exists( 'vsp_logger' ) ) {
	/**
	 * Returns An Valid Instance Of Logger To Handle Logs From VSP Framework.
	 *
	 * @return \\VSP\Modules\Logger
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

		if ( $handler instanceof \VSP\Modules\Logger && method_exists( $handler, $type ) ) {
			$handler->$type( $messages, $context );
			return true;
		} elseif ( vsp_logger() instanceof \VSP\Modules\Logger && method_exists( vsp_logger(), $type ) ) {
			$msg = array_merge( array( __( 'Tried To Log A Message But Failed Got unknown Handler', 'vsp-framework' ) ), wp_debug_backtrace_summary( null, 0, false ) );
			vsp_log_msg( '----------------------------------------------------------------', 'notice', vsp_logger() );
			vsp_log_msg( $msg, 'critical', vsp_logger() );
			vsp_log_msg( $messages, 'critical', vsp_logger() );
			vsp_log_msg( '----------------------------------------------------------------', 'notice', vsp_logger() );
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_date_format' ) ) {
	/**
	 * WooCommerce Date Format - Allows to change date format for everything WooCommerce.
	 *
	 * @return string
	 */
	function vsp_date_format() {
		return apply_filters( 'vsp_date_format', get_option( 'date_format' ) );
	}
}

if ( ! function_exists( 'vsp_time_format' ) ) {
	/**
	 * WooCommerce Time Format - Allows to change time format for everything WooCommerce.
	 *
	 * @return string
	 */
	function vsp_time_format() {
		return apply_filters( 'vsp_time_format', get_option( 'time_format' ) );
	}
}

if ( ! function_exists( 'vsp_censor_path' ) ) {
	/**
	 * Censors Actual Path and just provides path after that
	 *
	 * @param string $path
	 * @param bool   $actual_path
	 *
	 * @return mixed
	 * @example /var/www/html/wp-content/plugins will be returned as /wp-content/plugins
	 *
	 */
	function vsp_censor_path( $path = '', $actual_path = false ) {
		$actual_path = ( false === $actual_path ) ? ABSPATH : $actual_path;
		return str_replace( vsp_unslashit( $actual_path ), '', $path );
	}
}

if ( ! function_exists( 'vsp_json_last_error' ) ) {
	/**
	 * @return string|null
	 * @since 2.4.10
	 */
	function vsp_json_last_error() {
		switch ( function_exists( 'json_last_error' ) ? json_last_error() : -1 ) {
			case JSON_ERROR_NONE:
				return null; // __('No errors');
				break;
			case JSON_ERROR_DEPTH:
				return __( 'Maximum stack depth exceeded', 'vsp-framework' );
				break;
			case JSON_ERROR_STATE_MISMATCH:
				return __( 'Underflow or the modes mismatch', 'vsp-framework' );
				break;
			case JSON_ERROR_CTRL_CHAR:
				return __( 'Unexpected control character found', 'vsp-framework' );
				break;
			case JSON_ERROR_SYNTAX:
				return __( 'Syntax error, malformed JSON', 'vsp-framework' );
				break;
			case JSON_ERROR_UTF8:
				return __( 'Malformed UTF-8 characters, possibly incorrectly encoded', 'vsp-framework' );
				break;
			default:
				return __( 'Unknown error', 'vsp-framework' );
				break;
		}
	}
}

if ( ! function_exists( 'vsp_is_callable' ) ) {
	/**
	 * @param $callback
	 *
	 * @return bool
	 */
	function vsp_is_callable( $callback ) {
		if ( is_callable( $callback ) ) {
			return true;
		}
		if ( is_string( $callback ) && has_action( $callback ) ) {
			return true;
		}
		if ( is_string( $callback ) && has_filter( $callback ) ) {
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'vsp_callback' ) ) {
	/**
	 * @param       $callback
	 * @param array $args
	 *
	 * @return bool|false|mixed|string
	 */
	function vsp_callback( $callback, $args = array() ) {
		$data = false;
		try {
			if ( is_callable( $callback ) ) {
				$args = ( ! is_array( $args ) ) ? array( $args ) : $args;
				$data = call_user_func_array( $callback, $args );
			} elseif ( is_string( $callback ) && has_filter( $callback ) ) {
				$data = call_user_func_array( 'apply_filters', array_merge( array( $callback ), $args ) );
			} elseif ( is_string( $callback ) && has_action( $callback ) ) {
				ob_start();
				$args = ( ! is_array( $args ) ) ? array( $args ) : $args;
				echo call_user_func_array( 'do_action', array_merge( array( $callback ), $args ) );
				$data = ob_get_clean();
				ob_flush();
			}
		} catch ( Exception $exception ) {
			$data = false;
		}
		return $data;
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
