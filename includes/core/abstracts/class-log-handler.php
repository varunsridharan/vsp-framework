<?php

namespace VSP\Core\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Class VSP_Log_Handler
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Log_Handler implements \VSP\Core\Interfaces\Log_Handler {

	/**
	 * Builds a log entry text from level, timestamp and message.
	 *
	 * @param int    $timestamp Log timestamp.
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 * @param string $message Log message.
	 * @param array  $context Additional information for log handlers.
	 *
	 * @return string Formatted log entry.
	 */
	protected static function format_entry( $timestamp, $level, $message, $context ) {
		$time    = self::format_time( $timestamp );
		$level_s = strtoupper( $level );
		return apply_filters( 'vsp/log/format/entry', "{$time} {$level_s} {$message}", array(
			'timestamp' => $timestamp,
			'level'     => $level,
			'message'   => $message,
			'context'   => $context,
		) );
	}

	/**
	 * Formats a timestamp for use in log messages.
	 *
	 * @param int $timestamp Log timestamp.
	 *
	 * @return string Formatted time for use in log entry.
	 */
	protected static function format_time( $timestamp ) {
		return date( 'c', $timestamp );
	}
}
