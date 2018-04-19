<?php
/**
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 17-04-2018
 * Time 02:20 PM
 *
 * @package vsp-framework
 * @link http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_System_Logs' ) ) {
	/**
	 * Class VSP_System_Logs
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_System_Logs extends VSP_Class_Handler {
		protected static $logs        = array();
		protected static $actual_logs = array();

		/**
		 * VSP_System_Logs constructor.
		 *
		 * @param array $options
		 * @param array $defaults
		 */
		public function __construct( $options = array(), $defaults = array() ) {
			parent::__construct( $options, $defaults );
		}

		/**
		 * Outputs HTML.
		 *
		 * @static
		 */
		public static function render() {
			self::scan_log_files();


			if ( ! empty( $_REQUEST['log_file'] ) && isset( self::$logs[ sanitize_title( $_REQUEST['log_file'] ) ] ) ) {
				$viewed_log = self::$logs[ sanitize_title( $_REQUEST['log_file'] ) ];
			} elseif ( ! empty( self::$logs ) ) {
				$viewed_log = current( self::$logs );
			}
			$handle = ! empty( $viewed_log ) ? self::get_log_file_handle( $viewed_log ) : '';

			include( VSP_PATH . 'views/log-viewer.php' );

		}


		/**
		 * Scan the log files.
		 *
		 * @return array
		 */
		public static function scan_log_files() {
			$logs = array( '' => self::get_log_files( VSP_LOG_DIR ) );
			$logs = array_merge( self::get_nested_logs( VSP_LOG_DIR ), $logs );

			$fixed_logs = array();

			foreach ( $logs as $p => $ls ) {
				foreach ( $ls as $f => $n ) {
					$fixed_logs[ $f ] = trim( vsp_slashit( $p ) . $n, '/' );
				}
			}

			self::$logs        = $fixed_logs;
			self::$actual_logs = $logs;
			return self::$actual_logs;
		}

		/**
		 * Gets All Logs Files under a folder.
		 *
		 * @param string $path
		 * @param bool   $nested
		 *
		 * @return array
		 * @static
		 */
		public static function get_log_files( $path = '', $nested = false ) {
			$files  = @scandir( $path );
			$result = array();

			if ( ! empty( $files ) ) {
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( '.', '..' ) ) ) {
						if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
							$result[ sanitize_title( basename( $path ) . '/' . $value ) ] = $value;
						} elseif ( is_dir( VSP_LOG_DIR . $value ) && true === $nested ) {
							$result[ $value ] = self::get_log_files( VSP_LOG_DIR . $value );
						}
					}
				}
			}
			return $result;
		}

		/**
		 * Gets & returns nested logs under VPS_LOGS
		 *
		 * @param string $path
		 *
		 * @return array
		 * @static
		 */
		public static function get_nested_logs( $path = '' ) {
			$files  = @scandir( $path );
			$result = array();

			if ( ! empty( $files ) ) {
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( '.', '..' ) ) ) {
						if ( is_dir( VSP_LOG_DIR . $value ) ) {
							$result[ $value ] = self::get_log_files( VSP_LOG_DIR . $value, true );
						}
					}
				}
			}
			return $result;
		}

		/**
		 * Return the log file handle.
		 *
		 * @param string $filename
		 *
		 * @return string
		 */
		public static function get_log_file_handle( $filename ) {
			return substr( $filename, 0, strlen( $filename ) > 37 ? strlen( $filename ) - 37 : strlen( $filename ) - 4 );
		}
	}

}
