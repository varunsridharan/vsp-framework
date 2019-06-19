<?php

namespace VSP\Modules;

use VSP\Base;

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
	class System_Logs extends Base {
		/**
		 * @var bool
		 * @access
		 */
		protected $current = false;

		/**
		 * @var null
		 * @access
		 */
		protected $custom_path = null;

		/**
		 * @param      $filepath
		 * @param int  $lines
		 * @param bool $adaptive
		 *
		 * @return bool|string
		 * @static
		 */
		public function read_file( $filepath, $lines = 200, $adaptive = true ) {
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

			if ( fread( $f, 1 ) !== "\n" ) {
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
			$this->current     = ( isset( $_REQUEST['vsp-log-file'] ) ) ? $_REQUEST['vsp-log-file'] : false;
			$this->custom_path = $custom_path;

			if ( isset( $_REQUEST['delete-handle'] ) && ! empty( $_REQUEST['delete-handle'] ) ) {
				$this->remove_log();
			}

			include( VSP_PATH . 'views/log-viewer.php' );
		}

		/**
		 * Deletes A File.
		 */
		public function remove_log() {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'remove_log' ) ) { // WPCS: input var ok, sanitization ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'vsp-framework' ) );
			}
			if ( ! empty( $_REQUEST['delete-handle'] ) ) {  // WPCS: input var ok.
				$file = $_REQUEST['delete-handle'];
				if ( file_exists( VSP_LOG_DIR . $file ) ) {
					unlink( VSP_LOG_DIR . $file );
				}
			}
			wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'delete-handle', '_wpnonce' ) ) ) );
			exit();
		}


		/**
		 * Forces a log file as downloadable.
		 *
		 * @param $file
		 */
		public static function download_log( $file ) {
			header( 'Cache-Control: private' );
			header( 'Content-Type: application/stream' );
			if ( file_exists( VSP_LOG_DIR . $file ) ) {
				$size = filesize( VSP_LOG_DIR . $file );
				header( "Content-Disposition: attachment; filename=$file" );
				header( 'Content-Length: ' . $size );
				readfile( VSP_LOG_DIR . $file );
			} else {
				echo 'File Not Found';
			}

		}
	}
}
