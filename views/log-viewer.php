<?php
/**
 * Admin View: Page - Status Logs
 *
 * @package WooCommerce/Admin/Logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vsp_get_log_file_title' ) ) {
	function vsp_get_log_file_title( $file ) {
		$timestamp = filemtime( VSP_LOG_DIR . $file );
		return sprintf( __( ' ( %1$s at %2$s )', 'vsp-framework' ), date_i18n( vsp_date_format(), $timestamp ), date_i18n( vsp_time_format(), $timestamp ) );

	}
}
?>
<?php if ( $this->logs ) : ?>
	<div id="vsp-log-view-wrap">
		<div class="log-header">
			<style>
				.log-center div {
					padding: 0 !important;
					margin: 0 !important;
				}

				.btn {
					text-decoration: none;
				}
			</style>
			<div class="log-center">

				<?php
				echo wponion_add_element( array(
					'type'    => 'select',
					'name'    => 'log_files',
					'options' => function () {
						if ( false !== $this->subpath ) {
							$logs = isset( $this->actual_logs[ $this->subpath ] ) ? array_filter( $this->actual_logs[ $this->subpath ] ) : array();
							foreach ( $logs as $id => $val ) {
								if ( is_array( $val ) ) {
									foreach ( $val as $in => $v ) {
										$_file              = ( isset( $this->logs[ $in ] ) ) ? $this->logs[ $in ] : $in;
										$logs[ $id ][ $in ] = $v . vsp_get_log_file_title( $_file );
									}
								} else {
									$_file       = ( isset( $this->logs[ $id ] ) ) ? $this->logs[ $id ] : $id;
									$logs[ $id ] = $val . vsp_get_log_file_title( $_file );
								}
							}
							return $logs;
						} else {
							$return = array();
							$main   = __( 'VSP Framework', 'vsp-framework' );
							foreach ( $this->actual_logs as $group => $logs ) {
								$group = ( empty( $group ) ) ? $main : $group;
								foreach ( $logs as $file_id => $files ) {
									if ( is_array( $files ) ) {
										foreach ( $files as $i => $f ) {
											$logs[ $i ] = $file_id . ' / ' . $f;
										}
										unset( $logs[ $file_id ] );
									} else {
										foreach ( $logs as $fi => $name ) {
											$_file       = ( isset( $this->logs[ $fi ] ) ) ? $this->logs[ $fi ] : $fi;
											$logs[ $fi ] = $name . vsp_get_log_file_title( $_file );
										}
									}
								}
								$return[ $group ] = $logs;
							}
							return $return;
						}
					},
				) );
				?>
			</div>

			<div class="log-center">
				<h2>
					<?php
					if ( ! empty( $viewed_log ) ) {
						$_file = ( isset( $this->logs[ $viewed_log ] ) ) ? $this->logs[ $viewed_log ] : $viewed_log;
						echo $_file;
					}
					?>
				</h2>
				<?php
				if ( ! empty( $viewed_log ) ) {
					$href = wp_nonce_url( admin_url( 'admin-ajax.php?action=vsp_download_log&handle=' . $viewed_log ), 'download_log' );
					echo wponion_tooltip( __( 'Download' ), array(
						'element' => '<a href="' . $href . '" target="_blank" class="btn btn-secondary log-download-handle"><span class="dashicons dashicons-download"></span></a>',
					) );

					$href = wp_nonce_url( add_query_arg( 'delete-handle', $viewed_log ), 'remove_log' );
					echo wponion_tooltip( __( 'Delete Log' ), array(
						'element' => ' <a href="' . $href . '" class="btn btn-danger log-delete-handle"><span class="dashicons dashicons-trash"></span></a>',
					) );
				}
				?>
			</div>
		</div>
		<div class="log-viewer">
			<pre><?php echo esc_html( $this->read_file( $viewed_log, 1000 ) ); ?></pre>
		</div>
	</div>
<?php else : ?>
	<div class="notice inline notice-success notice-large notice-alt" style="margin: 0;">
		<p><?php esc_html_e( 'There Are No Logs Generated', 'vsp-framework' ); ?></p></div>
<?php endif; ?>
