<?php

namespace VSP\Core\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface VSP_Log_Handler_Interface
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
interface Log_Handler {
	/**
	 * Handle a log entry.
	 *
	 * @param int    $timestamp Log timestamp.
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 * @param string $message Log message.
	 * @param array  $context Additional information for log handlers.
	 *
	 * @return bool False if value was not handled and true if value was handled.
	 */
	public function handle( $timestamp, $level, $message, $context );
}
