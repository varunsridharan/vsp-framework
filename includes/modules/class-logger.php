<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

use Exception;

/**
 * Class VSP_Logger
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class Logger implements \VSP\Core\Interfaces\Logger {
	/**
	 * Stores registered log handlers.
	 *
	 * @var array
	 */
	protected $handlers;

	/**
	 * Minimum log level this handler will process.
	 *
	 * @var int Integer representation of minimum log level to handle.
	 */
	protected $threshold;

	/**
	 * Constructor for the logger.
	 *
	 * @param array  $handlers Optional. Array of log handlers. If $handlers is not provided,
	 *     the filter 'vsp_register_log_handlers' will be used to define the handlers.
	 *     If $handlers is provided, the filter will not be applied and the handlers will be
	 *     used directly.
	 * @param string $threshold Optional. Define an explicit threshold. May be configured
	 *     via  VSP_LOG_THRESHOLD. By default, all logs will be processed.
	 */
	public function __construct( $handlers = null, $threshold = null ) {
		if ( null === $handlers ) {
			$handlers = apply_filters( 'vsp/log/register/handlers', array() );
		}
		$register_handlers = array();
		if ( ! empty( $handlers ) && is_array( $handlers ) ) {
			foreach ( $handlers as $handler ) {
				$implements = class_implements( $handler );
				if ( is_object( $handler ) && is_array( $implements ) && in_array( 'VSP\Core\Interfaces\Log_Handler', $implements, true ) ) {
					$register_handlers[] = $handler;
				} else {
					/* translators: 1: class name 2: VSP_Log_Handler_Interface */
					vsp_doing_it_wrong( __METHOD__, sprintf( __( 'The provided handler %1$s does not implement %2$s.', 'vsp-framework' ), '<code>' . esc_html( is_object( $handler ) ? get_class( $handler ) : $handler ) . '</code>', '<code>VSP\Core\Interfaces\Log_Handler</code>' ), '3.0' );
				}
			}
		}
		if ( null !== $threshold ) {
			$threshold = Logger\Levels::get_level_severity( $threshold );
		} elseif ( defined( 'VSP_LOG_THRESHOLD' ) && Logger\Levels::is_valid_level( VSP_LOG_THRESHOLD ) ) {
			$threshold = Logger\Levels::get_level_severity( VSP_LOG_THRESHOLD );
		} else {
			$threshold = null;
		}
		$this->handlers  = $register_handlers;
		$this->threshold = $threshold;
		register_shutdown_function( array( $this, 'log_errors' ) );
	}

	/**
	 * Ensures fatal errors are logged so they can be picked up in the status report.
	 */
	public function log_errors() {
		$error = error_get_last();

		if ( E_ERROR === $error['type'] ) {
			vsp_log_msg( __( 'File & Line No :', 'vsp-framework' ) . $error['file'] . '-' . $error['line'], 'critical', false, array(
				'source' => 'fatal-errors',
			) );

			vsp_log_msg( $error['message'], 'critical', false, array(
				'source' => 'fatal-errors',
			) );
		}
	}

	/**
	 * Adds a critical level message.
	 *
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return $this
	 */
	public function critical( $message, $context = array() ) {
		$this->log( Logger\Levels::CRITICAL, $message, $context );
		return $this;
	}

	/**
	 * Add a log entry.
	 *
	 * @param string       $level One of the following:
	 *     'emergency': System is unusable.
	 *     'alert': Action must be taken immediately.
	 *     'critical': Critical conditions.
	 *     'error': Error conditions.
	 *     'warning': Warning conditions.
	 *     'notice': Normal but significant condition.
	 *     'info': Informational messages.
	 *     'debug': Debug-level messages.
	 * @param string|array $message Log message.
	 * @param array        $context Optional. Additional information for log handlers.
	 *
	 * @return $this
	 */
	public function log( $level, $message, $context = array() ) {
		try {
			if ( ! Logger\Levels::is_valid_level( $level ) ) {
				/* translators: 1: VSP_Logger::log 2: level */
				vsp_doing_it_wrong( __METHOD__, sprintf( __( '%1$s was called with an invalid level "%2$s".', 'vsp-framework' ), '<code>VSP_Logger::log</code>', $level ), '3.0' );
			}
			if ( $this->should_handle( $level ) ) {
				$timestamp = current_time( 'timestamp' );
				$message   = apply_filters( 'vsp/log/message', $message, $level, $context );

				if ( is_array( $message ) ) {
					$message = implode( PHP_EOL, $message );
				}

				foreach ( $this->handlers as $handler ) {
					$handler->handle( $timestamp, $level, $message, $context );
				}
			}
		} catch ( Exception$exception ) {
		}
		return $this;
	}

	/**
	 * Determine whether to handle or ignore log.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug
	 *
	 * @return bool True if the log should be handled.
	 */
	protected function should_handle( $level ) {
		if ( null === $this->threshold ) {
			return true;
		}
		return $this->threshold <= Logger\Levels::get_level_severity( $level );
	}

	/**
	 * Adds an emergency level message.
	 *
	 * System is unusable.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( Logger\Levels::EMERGENCY, $message, $context );
		return $this;
	}

	/**
	 * Adds an alert level message.
	 *
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function alert( $message, $context = array() ) {
		$this->log( Logger\Levels::ALERT, $message, $context );
		return $this;
	}

	/**
	 * Adds an error level message.
	 *
	 * Runtime errors that do not require immediate action but should typically be logged
	 * and monitored.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function error( $message, $context = array() ) {
		$this->log( Logger\Levels::ERROR, $message, $context );
		return $this;
	}

	/**
	 * Adds a warning level message.
	 *
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
	 * necessarily wrong.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function warning( $message, $context = array() ) {
		$this->log( Logger\Levels::WARNING, $message, $context );
		return $this;
	}

	/**
	 * Adds a notice level message.
	 *
	 * Normal but significant events.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function notice( $message, $context = array() ) {
		$this->log( Logger\Levels::NOTICE, $message, $context );
		return $this;
	}

	/**
	 * Adds a info level message.
	 *
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function info( $message, $context = array() ) {
		$this->log( Logger\Levels::INFO, $message, $context );
		return $this;
	}

	/**
	 * Adds a debug level message.
	 *
	 * Detailed debug information.
	 *
	 * @param string|array $message
	 * @param array        $context
	 *
	 * @return $this
	 */
	public function debug( $message, $context = array() ) {
		$this->log( Logger\Levels::DEBUG, $message, $context );
		return $this;
	}
}
