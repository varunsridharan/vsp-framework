<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

/**
 * Class Setup
 *
 * @package VSP
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class Setup {
	/**
	 * Handles File Setup Class.
	 */
	public static function init() {
		self::check_create_log_folder();
	}

	/**
	 * Checks if file exists if not creates the folder.
	 *
	 * @param bool $dir
	 */
	public static function check_create_log_folder( $dir = false ) {
		$dir = ( false === $dir ) ? VSP_LOG_DIR : $dir;
		if ( ! file_exists( vsp_slashit( $dir ) . 'index.html' ) ) {
			self::create_folders( $dir );
		}
	}

	/**
	 * Creates Log Folder If not exists.
	 *
	 * @param $dir
	 */
	public static function create_folders( $dir ) {
		$files = array(
			array(
				'base'    => $dir,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => $dir,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( vsp_slashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( vsp_slashit( $file['base'] ) . $file['file'], 'w' );
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}
}

Setup::init();
