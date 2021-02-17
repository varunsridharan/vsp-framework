<?php
global $current_file;
$plugin_files          = vsp_list_log_files( $this->custom_path );
$plugin_editable_files = array();
$filetoedit            = false;
foreach ( $plugin_files as $plugin_file ) {
	if ( preg_match( '/\.([^.]+)$/', $plugin_file, $matches ) && in_array( $matches[1], array( 'log', 'txt' ) ) ) {
		$plugin_editable_files[] = $plugin_file;
		if ( false !== $this->current && md5( $plugin_file ) === $this->current ) {
			$filetoedit = $plugin_file;
		}
	}
}

if ( empty( $plugin_editable_files ) ) {
	echo '<div class="vsp_nothing_found_wrap">
		<div class="vsp_nothing_found">
			' . wpo_icon( 'wpoic-file-text' ) . '
			<h2>' . esc_html__( 'No Logs Found', 'vsp-framework' ) . '</h2>
		</div>
	</div>';
} else {

	$filetoedit = ( false !== $filetoedit ) ? $filetoedit : $plugin_editable_files[0];
	$tree       = vsp_make_log_list_tree( $plugin_editable_files );
	$file       = validate_file_to_edit( $filetoedit, $plugin_files );
	$real_file  = VSP_LOG_DIR . $file;
	$content    = $this->read_file( $real_file, 1000 );
	$args       = wp_enqueue_code_editor( array(
		'file'        => $file,
		'lineNumbers' => false,
		'codemirror'  => array( 'readOnly' => true ),
	) );

	wp_enqueue_script( 'wp-theme-plugin-editor' );
	wp_add_inline_script( 'wp-theme-plugin-editor', sprintf( 'jQuery( function( $ ) { wp.themePluginEditor.init( $( "#template" ), %s ); } )', wp_json_encode( array(
		'codeEditor' => $args,
	) ) ) );
	wp_add_inline_script( 'wp-theme-plugin-editor', sprintf( 'wp.themePluginEditor.themeOrPlugin = "n";' ) );
	?>
	<div id="vsp-log-view-wrap">
		<div class="log-header">
			<div class="log-center">
				<h2><?php echo $file; ?></h2>
				<?php
				if ( ! empty( $file ) ) {
					$href = wp_nonce_url( admin_url( 'admin-ajax.php?action=vsp_download_log&handle=' . $file ), 'download_log' );
					echo wponion_tooltip( esc_html__( 'Download', 'vsp-framework' ), array(
						'element' => "<a href=\"$href\" target=\"_blank\" class=\"wpo-btn wpo-btn-secondary wpo-btn-sm log-download-handle\">" . wpo_icon( 'wpoic-file_download' ) . '</a>',
					) );

					$href = wp_nonce_url( add_query_arg( 'delete-handle', $file ), 'remove_log' );
					echo ' ' . wponion_tooltip( esc_html__( 'Delete Log', 'vsp-framework' ), array(
							'element' => "<a href=\"$href\" class=\"wpo-btn wpo-btn-danger wpo-btn-sm log-delete-handle\">" . wpo_icon( 'wpoic-delete' ) . '</a>',
						) );
				}
				?>
			</div>
		</div>
		<div id="templateside">
			<ul role="tree"
				aria-labelledby="plugin-files-label"><?php vsp_print_log_files_ui( $tree, $file ); ?></ul>
		</div>
		<div id="template"><textarea name="newcontent" id="newcontent"><?php echo $content; ?></textarea></div>
	</div>
	<?php
	wp_print_file_editor_templates();
}
