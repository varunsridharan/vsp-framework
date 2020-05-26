<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

use VSP\Base;

/**
 * Class VSP_System_Logs
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class System_Logs extends Base {
	/**
	 * @var bool
	 */
	protected $current = false;

	/**
	 * @var null
	 */
	protected $custom_path = null;

	/**
	 * Reads System Log.
	 *
	 * @param      $filepath
	 * @param int  $lines
	 * @param bool $adaptive
	 *
	 * @return bool|string
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
		if ( isset( $_REQUEST['delete-handle'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			if ( ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'remove_log' ) ) { // WPCS: input var ok, sanitization ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'vsp-framework' ) );
			}

			$file     = $_REQUEST['delete-handle'];
			$ff_regx  = '/\.([^.]+)$/';
			$ff_types = array( 'log', 'txt' );
			if ( preg_match( $ff_regx, $file, $m ) && in_array( $m[1], $ff_types, true ) ) {
				$files = vsp_list_log_files();
				foreach ( $files as $f ) {
					if ( preg_match( $ff_regx, $f, $m2 ) && in_array( $m2[1], $ff_types, true ) ) {
						if ( $f === $file && file_exists( VSP_LOG_DIR . $f ) ) {
							unlink( VSP_LOG_DIR . $f );
						}
					}
				}
			}
			wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'delete-handle', '_wpnonce' ) ) ) );
			exit();
		} else {
			wp_die( esc_html__( 'Invalid wpnonce or log file not found!.', 'vsp-framework' ) );
		}
	}
}
