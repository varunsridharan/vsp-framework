<?php
/**
 * EMail Log Handler.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 13-04-2018
 * Time 03:17 PM
 *
 * @package   vsp-framework/core/modules/logger
 * @link      http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */


namespace VSP\Modules\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Log_Handler_Email
 *
 * WARNING!
 * This log handler has known limitations.
 *
 * Log messages are aggregated and sent once per request (if necessary). If the site experiences a
 * problem, the log email may never be sent. This handler should be used with another handler which
 * stores logs in order to prevent loss.
 *
 * It is not recommended to use this handler on a high traffic site. There will be a maximum of 1
 * email sent per request per handler, but that could still be a dangerous amount of emails under
 * heavy traffic. Do not confuse this handler with an appropriate monitoring solution!
 *
 * If you understand these limitations, feel free to use this handler or borrow parts of the design
 * to implement your own!
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class Email_Handler extends \VSP\Core\Abstracts\Log_Handler {
	/**
	 * Minimum log level this handler will process.
	 *
	 * @var int Integer representation of minimum log level to handle.
	 */
	protected $threshold;

	/**
	 * Stores email recipients.
	 *
	 * @var array
	 */
	protected $recipients = array();

	/**
	 * Stores log messages.
	 *
	 * @var array
	 */
	protected $logs = array();

	/**
	 * Stores integer representation of maximum logged level.
	 *
	 * @var int
	 */
	protected $max_severity = null;

	/**
	 * Constructor for log handler.
	 *
	 * @param string|array $recipients Optional. Email(s) to receive log messages. Defaults to site admin email.
	 * @param string       $threshold Optional. Minimum level that should receive log messages.
	 *           Default 'alert'. One of: emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public function __construct( $recipients = null, $threshold = 'alert' ) {
		if ( is_array( $recipients ) ) {
			foreach ( $recipients as $recipient ) {
				$this->add_email( $recipient );
			}
		} else {
			$this->add_email( $recipients );
		}
		$this->set_threshold( $threshold );
		add_action( 'shutdown', array( $this, 'send_log_email' ) );
	}

	/**
	 * Adds an email to the list of recipients.
	 *
	 * @param string $email Email address to add.
	 */
	public function add_email( $email ) {
		array_push( $this->recipients, $email );
	}

	/**
	 * Set handler severity threshold.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public function set_threshold( $level ) {
		$this->threshold = Levels::get_level_severity( $level );
	}

	/**
	 * Handle a log entry.
	 *
	 * @param int    $timestamp Log timestamp.
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 *
	 * @return bool False if value was not handled and true if value was handled.
	 */
	public function handle( $timestamp, $level, $message, $context ) {
		if ( $this->should_handle( $level ) ) {
			$this->add_log( $timestamp, $level, $message, $context );
			return true;
		}
		return false;
	}

	/**
	 * Determine whether handler should handle log.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 *
	 * @return bool True if the log should be handled.
	 */
	protected function should_handle( $level ) {
		return $this->threshold <= Levels::get_level_severity( $level );
	}

	/**
	 * Add log message.
	 *
	 * @param int    $timestamp Log timestamp.
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 * @param string $message Log message.
	 * @param array  $context Additional information for log handlers.
	 */
	protected function add_log( $timestamp, $level, $message, $context ) {
		$this->logs[] = $this->format_entry( $timestamp, $level, $message, $context );
		$log_severity = Levels::get_level_severity( $level );
		if ( $this->max_severity < $log_severity ) {
			$this->max_severity = $log_severity;
		}
	}

	/**
	 * Send log email.
	 *
	 * @return bool True if email is successfully sent otherwise false.
	 */
	public function send_log_email() {
		$result = false;
		if ( ! empty( $this->logs ) ) {
			$subject = $this->get_subject();
			$body    = $this->get_body();
			$result  = wp_mail( $this->recipients, $subject, $body );
			$this->clear_logs();
		}
		return $result;
	}

	/**
	 * Build subject for log email.
	 *
	 * @return string subject
	 */
	protected function get_subject() {
		$site_name = get_bloginfo( 'name' );
		$max_level = strtoupper( Levels::get_severity_level( $this->max_severity ) );
		$log_count = count( $this->logs );
		return sprintf( /* translators: 1: Site name 2: Maximum level 3: Log count */
			_n( '[%1$s] %2$s: %3$s log message', '[%1$s] %2$s: %3$s log messages', $log_count, 'vsp-framework' ), $site_name, $max_level, $log_count );
	}

	/**
	 * Build body for log email.
	 *
	 * @return string body
	 */
	protected function get_body() {
		$site_name = get_bloginfo( 'name' );
		$entries   = implode( PHP_EOL, $this->logs );
		$log_count = count( $this->logs );
		return _n( 'You have received the following log message:', 'You have received the following log messages:', $log_count, 'vsp-framework' ) . PHP_EOL . PHP_EOL . $entries . PHP_EOL . PHP_EOL /* translators: %s: Site name */ . sprintf( __( 'Visit %s admin area:', 'woocommerce' ), $site_name ) . PHP_EOL . admin_url();
	}

	/**
	 * Clear log messages.
	 */
	protected function clear_logs() {
		$this->logs = array();
	}

}
