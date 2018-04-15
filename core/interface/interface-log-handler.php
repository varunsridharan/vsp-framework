<?php
/**
 * Log Handler Interface
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 13-04-2018
 * Time 03:04 PM
 *
 * @package   vsp-framework/core/interface
 * @link      http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Interface VSP_Log_Handler_Interface
 * Functions that must be defined to correctly fulfill log handler API.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
interface VSP_Log_Handler_Interface {
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