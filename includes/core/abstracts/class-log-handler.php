<?php
/**
 * Log handling functionality.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 13-04-2018
 * Time 03:02 PM
 *
 * @package   vsp-framework/core/abstract
 * @link      http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

namespace VSP\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Log_Handler
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
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
		$time_string  = self::format_time( $timestamp );
		$level_string = strtoupper( $level );
		$entry        = "{$time_string} {$level_string} {$message}";
		return apply_filters( 'vsp_format_log_entry', $entry, array(
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
