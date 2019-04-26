<?php
/**
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 17-04-2018
 * Time 02:20 PM
 *
 * @package   vsp-framework/core/modules/system-tools
 * @link http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */


namespace VSP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'System_Logs' ) ) {
	/**
	 * Class VSP_System_Logs
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class System_Logs extends \VSP\Base {
		/**
		 * @var array
		 */
		protected $logs = array();

		/**
		 * @var array
		 */
		protected $actual_logs = array();

		/**
		 * @var bool
		 * @access
		 */
		protected $subpath = false;

		/**
		 * @param      $filepath
		 * @param int  $lines
		 * @param bool $adaptive
		 *
		 * @return bool|string
		 * @static
		 */
		public function read_file( $filepath, $lines = 1, $adaptive = true ) {
			$filepath = ( isset( $this->logs[ $filepath ] ) ) ? $this->logs[ $filepath ] : $filepath;
			$filepath = VSP_LOG_DIR . $filepath;
			// Open file
			$f = @fopen( $filepath, 'rb' );

			if ( false === $f ) {
				return false;
			}

			if ( ! $adaptive ) {
				$buffer = 4096;
			} else {
				$buffer = ( $lines < 2 ? 64 : ( $lines < 10 ? 512 : 4096 ) );
			}

			fseek( $f, -1, SEEK_END );
			if ( fread( $f, 1 ) != "\n" ) {
				$lines -= 1;
			}

			$output = '';
			while ( ftell( $f ) > 0 && $lines >= 0 ) {
				$seek = min( ftell( $f ), $buffer );
				fseek( $f, -$seek, SEEK_CUR );
				$chunk  = fread( $f, $seek );
				$output = $chunk . $output;
				fseek( $f, -mb_strlen( $chunk, '8bit' ), SEEK_CUR );
				$lines -= substr_count( $chunk, "\n" );
			}

			while ( $lines++ < 0 ) {
				$output = substr( $output, strpos( $output, "\n" ) + 1 );
			}
			fclose( $f );
			return trim( $output );
		}

		/**
		 * Outputs HTML.
		 *
		 * @param bool $custom_path
		 */
		public function render( $custom_path = false ) {
			$this->subpath = $custom_path;
			$this->scan_log_files();
			$file_removed = false;
			if ( ! empty( $_REQUEST['log_files'] ) && isset( $this->logs[ sanitize_title( $_REQUEST['log_files'] ) ] ) ) {
				$viewed_log = sanitize_title( $_REQUEST['log_files'] );
			} elseif ( ! empty( $this->logs ) ) {
				$viewed_log = current( array_keys( $this->logs ) );
			}

			if ( isset( $_REQUEST['delete-handle'] ) && ! empty( $_REQUEST['delete-handle'] ) ) {
				$this->remove_log();
				$file_removed = true;
			}

			include( VSP_PATH . 'views/log-viewer.php' );
		}

		/**
		 * Deletes A File.
		 */
		public function remove_log() {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'remove_log' ) ) { // WPCS: input var ok, sanitization ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.' ) );
			}

			if ( ! empty( $_REQUEST['delete-handle'] ) ) {  // WPCS: input var ok.
				if ( isset( $this->logs[ $_REQUEST['delete-handle'] ] ) ) {
					$file = $this->logs[ $_REQUEST['delete-handle'] ];

					foreach ( $this->actual_logs as $group => $data ) {
						if ( isset( $data[ $_REQUEST['delete-handle'] ] ) ) {
							unset( $data[ $_REQUEST['delete-handle'] ] );
							$this->actual_logs[ $group ] = $data;
							break;
						}
					}
					if ( file_exists( VSP_LOG_DIR . $file ) ) {
						unlink( VSP_LOG_DIR . $file );
					}
				}
			}
			wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'delete-handle', '_wpnonce' ) ) ) );
			exit();
		}

		/**
		 * Scan For Log Files.
		 *
		 * @param $custom_path
		 *
		 * @static
		 * @return array
		 */
		public function scan_log_files( $custom_path = false ) {
			if ( false === $this->subpath ) {
				$logs = array( '' => $this->get_log_files( VSP_LOG_DIR ) );
				$logs = array_merge( $this->get_nested_logs( VSP_LOG_DIR ), $logs );
			} else {
				$logs = array( $this->subpath => $this->get_log_files( VSP_LOG_DIR . $this->subpath, true ) );
			}
			$fixed_logs = array();

			foreach ( $logs as $group => $files ) {
				if ( is_array( $files ) ) {
					foreach ( $files as $file_name => $file ) {
						if ( is_array( $file ) ) {
							foreach ( $file as $file_id => $_file ) {
								$fixed_logs[ $file_id ] = trim( vsp_slashit( $group ) . vsp_slashit( $file_name ) . $_file );
							}
						} else {
							if ( ! empty( $group ) ) {
								$fixed_logs[ $file_name ] = trim( vsp_slashit( $group ) . $file );
							} else {
								$fixed_logs[ $file_name ] = trim( $file );
							}
						}
					}
				}
			}

			$this->logs        = $fixed_logs;
			$this->actual_logs = $logs;
			return $this->actual_logs;
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
		public function get_log_files( $path = '', $nested = false ) {
			$files  = @scandir( $path );
			$result = array();

			if ( ! empty( $files ) ) {
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( '.', '..' ) ) ) {
						if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
							$result[ md5( $nested . '/' . basename( $path ) . '/' . $value ) ] = $value;
						} elseif ( is_dir( trailingslashit( $path ) . $value ) && false !== $nested ) {
							$result[ $value ] = self::get_log_files( trailingslashit( $path ) . $value, basename( $path ) );
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
		public function get_nested_logs( $path = '' ) {
			$files  = @scandir( $path );
			$result = array();

			if ( ! empty( $files ) ) {
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( '.', '..' ) ) ) {
						if ( is_dir( VSP_LOG_DIR . $value ) ) {
							$result[ $value ] = $this->get_log_files( VSP_LOG_DIR . $value, true );
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
		public function get_log_file_handle( $filename ) {
			return substr( $filename, 0, strlen( $filename ) > 37 ? strlen( $filename ) - 37 : strlen( $filename ) - 4 );
		}

		public function get_logs() {
			return $this->logs;
		}

		/**
		 * Forces a log file as downloadable.
		 *
		 * @param $file
		 */
		public static function download_log( $file ) {
			$instance = new self();
			$instance->scan_log_files();
			foreach ( $instance->get_logs() as $file_key => $_file ) {
				$size = filesize( VSP_LOG_DIR . $_file );
				if ( $file === $file_key ) {
					header( 'Cache-Control: private' );
					header( 'Content-Type: application/stream' );
					header( 'Content-Length: ' . $size );
					header( "Content-Disposition: attachment; filename=$file" );
					readfile( VSP_LOG_DIR . $_file );
				}
			}
		}
	}
}
