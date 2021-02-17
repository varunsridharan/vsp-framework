<?php

namespace VSP\Modules\Logger;

defined( 'ABSPATH' ) || exit;

use VSP\Core\Abstracts\Log_Handler;
use VSP\Setup;

/**
 * Class VSP_Log_Handler_File
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class File_Handler extends Log_Handler {
	/**
	 * sub_path
	 *
	 * @var bool
	 */
	protected $sub_path = false;

	/**
	 * Stores open file handles.
	 *
	 * @var array
	 */
	protected $handles = array();

	/**
	 * File size limit for log files in bytes.
	 *
	 * @var int
	 */
	protected $log_size_limit;

	/**
	 * Cache logs that could not be written.
	 *
	 * If a log is written too early in the request, pluggable functions may be unavailable. These
	 * logs will be cached and written on 'plugins_loaded' action.
	 *
	 * @var array
	 */
	protected $cached_logs = array();

	/**
	 * @var null
	 */
	protected $file_name = null;

	/**
	 * VSP_Log_Handler_File constructor.
	 *
	 * @param bool                $sub_path
	 * @param string|boolean|null $file_name
	 * @param int                 $log_size_limit Optional. Size limit for log files. Default 5mb.
	 */
	public function __construct( $sub_path = false, $file_name = null, $log_size_limit = null ) {
		if ( null === $log_size_limit || false === $log_size_limit ) {
			$log_size_limit = 5 * 1024 * 1024;
		}

		$this->log_size_limit = $log_size_limit;
		$this->sub_path       = $sub_path;
		$this->file_name      = $file_name;
		add_action( 'vsp/init', array( $this, 'write_cached_logs' ), 1 );
	}

	/**
	 * Destructor.
	 *
	 * Cleans up open file handles.
	 */
	public function __destruct() {
		foreach ( $this->handles as $handle ) {
			if ( is_resource( $handle ) ) {
				fclose( $handle );
			}
		}
	}

	/**
	 * Handle a log entry.
	 *
	 * @param int    $timestamp Log timestamp.
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 * @param string $message Log message.
	 * @param array  $context {
	 *      Additional information for log handlers.
	 *
	 * @type string  $source Optional. Determines log file to write to. Default 'log'.
	 * @type bool    $_legacy Optional. Default false. True to use outdated log format
	 *         originally used in deprecated VSP_Logger::add calls.
	 * }
	 *
	 * @return bool False if value was not handled and true if value was handled.
	 */
	public function handle( $timestamp, $level, $message, $context ) {
		if ( isset( $context['source'] ) && $context['source'] ) {
			$handle = $context['source'];
		} else {
			$handle = 'log';
		}
		$entry = self::format_entry( $timestamp, $level, $message, $context );
		return $this->add( $entry, $handle );
	}

	/**
	 * Builds a log entry text from timestamp, level and message.
	 *
	 * @param int    $timestamp Log timestamp.
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug.
	 * @param string $message Log message.
	 * @param array  $context Additional information for log handlers.
	 *
	 * @return string Formatted log entry.
	 */
	protected static function format_entry( $timestamp, $level, $message, $context ) {
		if ( isset( $context['_legacy'] ) && true === $context['_legacy'] ) {
			if ( isset( $context['source'] ) && $context['source'] ) {
				$handle = $context['source'];
			} else {
				$handle = 'log';
			}
			$message = apply_filters( 'vsp/log/add/message', $message, $handle );
			$time    = date_i18n( 'm-d-Y @ H:i:s' );
			$entry   = "{$time} - {$message}";
		} else {
			$entry = parent::format_entry( $timestamp, $level, $message, $context );
		}

		return $entry;
	}

	/**
	 * Add a log entry to chosen file.
	 *
	 * @param string $entry Log entry text.
	 * @param string $handle Log entry handle.
	 *
	 * @return bool True if write was successful.
	 */
	protected function add( $entry, $handle ) {
		$result = false;
		if ( $this->should_rotate( $handle ) ) {
			$this->log_rotate( $handle );
		}

		if ( $this->open( $handle ) && is_resource( $this->handles[ $handle ] ) ) {
			$result = fwrite( $this->handles[ $handle ], $entry . PHP_EOL );
		} else {
			$this->cache_log( $entry, $handle );
		}
		return false !== $result;
	}

	/**
	 * Check if log file should be rotated.
	 *
	 * Compares the size of the log file to determine whether it is over the size limit.
	 *
	 * @param string $handle Log handle.
	 *
	 * @return bool True if if should be rotated.
	 */
	protected function should_rotate( $handle ) {
		$file = $this->get_log_file_path( $handle );
		if ( $file ) {
			if ( $this->is_open( $handle ) ) {
				$file_stat = fstat( $this->handles[ $handle ] );
				return $file_stat['size'] > $this->log_size_limit;
			} elseif ( file_exists( $file ) ) {
				return filesize( $file ) > $this->log_size_limit;
			}
		}
		return false;
	}

	/**
	 * Get a log file path.
	 *
	 * @param string $handle Log name.
	 *
	 * @return bool|string The log file path or false if path cannot be determined.
	 */
	public function get_log_file_path( $handle ) {
		if ( function_exists( 'wp_hash' ) ) {
			if ( false !== $this->sub_path ) {
				Setup::check_create_log_folder( vsp_slashit( VSP_LOG_DIR ) . $this->sub_path );
				return vsp_slashit( VSP_LOG_DIR ) . vsp_slashit( $this->sub_path ) . $this->get_log_file_name( $handle );
			} else {
				Setup::check_create_log_folder( vsp_slashit( VSP_LOG_DIR ) );
				return vsp_slashit( VSP_LOG_DIR ) . $this->get_log_file_name( $handle );
			}
		} else {
			vsp_doing_it_wrong( __METHOD__, esc_html__( 'This method should not be called before plugins_loaded.', 'vsp-framework' ), '3.0' );
			return false;
		}
	}

	/**
	 * Get a log file name.
	 *
	 * @param string $handle Log name.
	 *
	 * @return bool|string The log file name or false if cannot be determined.
	 */
	public function get_log_file_name( $handle ) {
		if ( function_exists( 'wp_hash' ) ) {
			if ( empty( $this->file_name ) ) {
				return sanitize_file_name( $handle . '-' . wp_hash( $handle . '_' . $this->sub_path ) . '.log' );
			} else {
				return sanitize_file_name( $this->file_name . '-' . wp_hash( $handle . '_' . $this->sub_path ) . '.log' );
			}
		} else {
			vsp_doing_it_wrong( __METHOD__, esc_html__( 'This method should not be called before plugins_loaded.', 'vsp-framework' ), '3.3' );
			return false;
		}
	}

	/**
	 * Check if a handle is open.
	 *
	 * @param string $handle Log handle.
	 *
	 * @return bool True if $handle is open.
	 */
	protected function is_open( $handle ) {
		return array_key_exists( $handle, $this->handles ) && is_resource( $this->handles[ $handle ] );
	}

	/**
	 * Rotate log files.
	 *
	 * Logs are rotated by prepending '.x' to the '.log' suffix.
	 * The current log plus 10 historical logs are maintained.
	 * For example:
	 *     base.9.log -> [ REMOVED ]
	 *     base.8.log -> base.9.log
	 *     ...
	 *     base.0.log -> base.1.log
	 *     base.log   -> base.0.log
	 *
	 * @param string $handle Log handle.
	 */
	protected function log_rotate( $handle ) {
		for ( $i = 8; $i >= 0; $i-- ) {
			$this->increment_log_infix( $handle, $i );
		}
		$this->increment_log_infix( $handle );
	}

	/**
	 * Increment a log file suffix.
	 *
	 * @param string   $handle Log handle.
	 * @param null|int $number Optional. Default null. Log suffix number to be incremented.
	 *
	 * @return bool True if increment was successful, otherwise false.
	 */
	protected function increment_log_infix( $handle, $number = null ) {
		if ( null === $number ) {
			$suffix      = '';
			$next_suffix = '.0';
		} else {
			$suffix      = '.' . $number;
			$next_suffix = '.' . ( $number + 1 );
		}
		$rename_from = $this->get_log_file_path( "{$handle}{$suffix}" );
		$rename_to   = $this->get_log_file_path( "{$handle}{$next_suffix}" );
		if ( $this->is_open( $rename_from ) ) {
			$this->close( $rename_from );
		}
		if ( is_writable( $rename_from ) ) { // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_is_writable
			return rename( $rename_from, $rename_to ); // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_rename
		} else {
			return false;
		}
	}

	/**
	 * Close a handle.
	 *
	 * @param string $handle Log handle.
	 *
	 * @return bool success
	 */
	protected function close( $handle ) {
		$result = false;
		if ( $this->is_open( $handle ) ) {
			$result = fclose( $this->handles[ $handle ] );
			unset( $this->handles[ $handle ] );
		}
		return $result;
	}

	/**
	 * Open log file for writing.
	 *
	 * @param string $handle Log handle.
	 * @param string $mode Optional. File mode. Default 'a'.
	 *
	 * @return bool Success.
	 */
	protected function open( $handle, $mode = 'a' ) {
		if ( $this->is_open( $handle ) ) {
			return true;
		}
		$file = $this->get_log_file_path( $handle );
		if ( $file ) {
			if ( ! file_exists( $file ) ) {
				$temphandle = @fopen( $file, 'w+' );
				@fclose( $temphandle );
				if ( defined( 'FS_CHMOD_FILE' ) ) {
					@chmod( $file, FS_CHMOD_FILE );
				}
			}
			$resource = @fopen( $file, $mode );
			if ( $resource ) {
				$this->handles[ $handle ] = $resource;
				return true;
			}
		}
		return false;
	}

	/**
	 * Cache log to write later.
	 *
	 * @param string $entry Log entry text.
	 * @param string $handle Log entry handle.
	 */
	protected function cache_log( $entry, $handle ) {
		$this->cached_logs[] = array(
			'entry'  => $entry,
			'handle' => $handle,
		);
	}

	/**
	 * Clear entries from chosen file.
	 *
	 * @param string $handle Log handle.
	 *
	 * @return bool
	 */
	public function clear( $handle ) {
		$result = false;
		$this->close( $handle );
		if ( $this->open( $handle, 'w' ) && is_resource( $this->handles[ $handle ] ) ) {
			$result = true;
		}
		do_action( 'vsp/log/clear', $handle );
		return $result;
	}

	/**
	 * Remove/delete the chosen file.
	 *
	 * @param string $handle Log handle.
	 *
	 * @return bool
	 */
	public function remove( $handle ) {
		$removed = false;
		$file    = $this->get_log_file_path( $handle );
		if ( $file ) {
			if ( is_file( $file ) && is_writable( $file ) ) { // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_is_writable
				$this->close( $handle ); // Close first to be certain no processes keep it alive after it is unlinked.
				$removed = unlink( $file ); // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink
			}
			do_action( 'vsp/log/remove', $handle, $removed );
		}
		return $removed;
	}

	/**
	 * Write cached logs.
	 */
	public function write_cached_logs() {
		foreach ( $this->cached_logs as $log ) {
			$this->add( $log['entry'], $log['handle'] );
		}
	}
}
