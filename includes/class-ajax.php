<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

use Varunsridharan\WordPress\Ajaxer;

/**
 * Class VSP_Core_Ajax
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
final class Ajax extends Ajaxer {
	/**
	 * Ajax Action Prefix
	 *
	 * @var string
	 */
	protected $action_prefix = 'vsp';

	/**
	 * Ajax actions
	 *
	 * @var array
	 */
	protected $actions = array(
		'addon_action' => false,
		'download_log' => true,
	);

	/**
	 * Handles Ajax Request
	 */
	public function addon_action() {
		if ( $this->has_request( 'hook_slug' ) ) {
			$this->validate_request( 'addon_action', esc_html__( 'Addon Action Not Provided', 'vsp-framework' ) );
			$this->validate_request( 'addon', esc_html__( 'Unable To Process Your Request', 'vsp-framework' ) );
			do_action( $_REQUEST['hook_slug'] . '/addon/ajax/handle/request', $this );
		}
		$this->json_error();
	}

	/**
	 * Handles Log Download.
	 */
	public function download_log() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			$this->error( esc_html__( 'Invalid Nonce', 'vsp-framework' ) );
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'download_log' ) ) {
			$this->error( esc_html__( 'Nonce Expired', 'vsp-framework' ) );
		}

		if ( isset( $_REQUEST['handle'] ) && ! empty( $_REQUEST['handle'] ) ) {
			$file     = $_REQUEST['handle'];
			$ff_regx  = '/\.([^.]+)$/';
			$ff_types = array( 'log', 'txt' );
			if ( preg_match( $ff_regx, $file, $m ) && in_array( $m[1], $ff_types, true ) ) {
				$files = vsp_list_log_files();
				foreach ( $files as $f ) {
					if ( preg_match( $ff_regx, $f, $m2 ) && in_array( $m2[1], $ff_types, true ) ) {
						if ( $f === $file && file_exists( VSP_LOG_DIR . $f ) ) {
							header( 'Cache-Control: private' );
							header( 'Content-Type: application/stream' );
							$size = filesize( VSP_LOG_DIR . $f );
							header( "Content-Disposition: attachment; filename=$f" );
							header( 'Content-Length: ' . $size );
							readfile( VSP_LOG_DIR . $f );
							wp_die();
						}
					}
				}
				$this->error( esc_html__( 'Log File Not Found !', 'vsp-framework' ) );
			} else {
				$this->error( esc_html__( 'Invalid Log File Extension', 'vsp-framework' ) );
			}
		} else {
			$this->error( esc_html__( 'Invalid Log File', 'vsp-framework' ) );
		}
		wp_die();
	}
}

return new Ajax();
