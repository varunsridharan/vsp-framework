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

	<style>
		#log-viewer {
			padding:       10px;
			background:    white;
			border:        1px solid #dedede;
			border-radius: 5px;
			margin-top:    10px;
		}

		#log-viewer pre {
			margin:  0;
			padding: 5px;
		}

		#log-viewer-select .alignleft h2 {
			font-size:   16px;
			font-weight: bold;
		}

	</style>
	<div id="log-viewer-select">
		<div class="alignleft">
			<h2>
				<?php echo esc_html( $viewed_log ); ?>
				<?php if ( ! empty( $handle ) ) : ?>
					<a class="page-title-action"
					   href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'handle' => $handle ), admin_url( 'admin.php?page=wc-status&tab=logs' ) ), 'remove_log' ) ); ?>"
					   class="button"><?php esc_html_e( 'Delete log', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</h2>
		</div>
		<div class="alignright">
			</form>
			<form method="post">
				<select name="log_file">

					<?php
					foreach ( self::$actual_logs as $group => $logs ):
						$group = ( '' === $group ) ? __( 'VSP Framework', 'vsp-framework' ) : $group;
						echo '<optgroup label="' . $group . '">';
						foreach ( $logs as $key => $log_file ):
							$_file = ( isset( self::$logs[ $key ] ) ) ? self::$logs[ $key ] : $log_file;
							$timestamp = filemtime( VSP_LOG_DIR . $_file );
							/* translators: 1: last access date 2: last access time */
							$date = sprintf( __( '%1$s at %2$s', 'vsp-framework' ), date_i18n( vsp_date_format(), $timestamp ), date_i18n( vsp_time_format(), $timestamp ) );
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( sanitize_title( $viewed_log ), $key ); ?>><?php echo esc_html( $log_file ); ?>
								(<?php echo esc_html( $date ); ?>)
							</option>
						<?php
						endforeach;
						echo '</optgroup>';
					endforeach;
					?>
				</select>
				<button type="submit" class="button"
						value="<?php esc_attr_e( 'View', 'vsp-framework' ); ?>"><?php esc_html_e( 'View', 'vsp-framework' ); ?></button>
			</form>
		</div>
		<div class="clear"></div>
	</div>
	<div id="log-viewer">
		<pre><?php echo esc_html( file_get_contents( VSP_LOG_DIR . $viewed_log ) ); ?></pre>
	</div>
<?php else : ?>
	<div class="updated woocommerce-message inline">
		<p><?php esc_html_e( 'There are currently no logs to view.', 'vsp-framework' ); ?></p></div>
<?php endif; ?>
