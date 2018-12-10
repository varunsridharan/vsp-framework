<?php
/**
 * VSP Framework Base Setup.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * Date 14-04-2018
 * Time 10:36 AM
 * @package vsp-framework/core
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Framework_Setup
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
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
	 *
	 * @static
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
	 *
	 * @static
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
				if ( $file_handle = @fopen( vsp_slashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}
}

Setup::init();
