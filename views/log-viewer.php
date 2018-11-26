<?php
/**
 * Admin View: Page - Status Logs
 *
 * @package WooCommerce/Admin/Logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( self::$logs ) : ?>
	<div id="vsp-log-view-wrap">
		<div class="log-header">
			<div class="log-center">
				<select name="log_file">
					<?php
					foreach ( self::$actual_logs as $group => $log ) {
						$group = ( '' === $group ) ? __( 'VSP Framework', 'vsp-framework' ) : $group;
						echo '<optgroup label="' . $group . '">';
						foreach ( $log as $key => $file ) {
							$_file     = ( isset( self::$logs[ $key ] ) ) ? self::$logs[ $key ] : $file;
							$timestamp = filemtime( VSP_LOG_DIR . $_file );
							/* translators: 1: last access date 2: last access time */
							$date = sprintf( __( '%1$s at %2$s', 'vsp-framework' ), date_i18n( vsp_date_format(), $timestamp ), date_i18n( vsp_time_format(), $timestamp ) );
							echo '<option value="' . esc_attr( $key ) . '" ' . selected( sanitize_title( $viewed_log ), $key ) . '>' . esc_html( $file ) . '(' . esc_html( $date ) . ')</option>';
						}
						echo '</optgroup>';
					}
					?>
				</select>
				<?php
				if ( ! empty( $viewed_log ) ) {
					$href = wp_nonce_url( add_query_arg( 'handle', $viewed_log ), 'remove_log' );
					echo ' <a href="' . $href . '" class="button log-delete-handle text-danger">' . __( 'Delete Log' ) . '</a>';
				}
				?>
			</div>

			<div class="log-center">
				<h2><?php echo $viewed_log; ?></h2>
				<?php
				if ( ! empty( $handle ) ) {
					$href = wp_nonce_url( admin_url( 'admin-ajax.php?action=vsp_download_log&handle=' . $viewed_log ), 'download_log' );
					echo ' <a href="' . $href . '" target="_blank" class="button log-download-handle">' . __( 'Download' ) . '</a>';
				}
				?>
			</div>
		</div>
		<div class="log-viewer">
			<pre><?php echo esc_html( self::read_file( VSP_LOG_DIR . $viewed_log, 1000 ) ); ?></pre>
		</div>
	</div>
<?php else : ?>
	<div class="notice inline notice-success notice-large notice-alt" style="margin: 0;">
		<p><?php esc_html_e( 'There Are No Logs Generated', 'vsp-framework' ); ?></p></div>
<?php endif; ?>
