<?php
if ( ! function_exists( 'vsp_print_log_files_ui' ) ) {
	/**
	 * @param        $tree
	 * @param string $current_file
	 * @param string $label
	 * @param int    $level
	 * @param int    $size
	 * @param int    $index
	 */
	function vsp_print_log_files_ui( $tree, $current_file = '', $label = '', $level = 2, $size = 1, $index = 1 ) {
		if ( is_array( $tree ) ) {
			$index = 0;
			$size  = count( $tree );
			foreach ( $tree as $label => $plugin_file ) :
				$index++;
				if ( ! is_array( $plugin_file ) ) {
					vsp_print_log_files_ui( $plugin_file, $current_file, $label, $level, $index, $size );
					continue;
				}
				?>
				<li role="treeitem" aria-expanded="true" tabindex="-1"
					aria-level="<?php echo esc_attr( $level ); ?>"
					aria-setsize="<?php echo esc_attr( $size ); ?>"
					aria-posinset="<?php echo esc_attr( $index ); ?>">
					<span class="folder-label"><?php echo esc_html( $label ); ?> <span
								class="screen-reader-text"><?php _e( 'folder' ); ?></span>
						<span aria-hidden="true" class="icon"></span></span>
					<ul role="group" class="tree-folder">
						<?php vsp_print_log_files_ui( $plugin_file, $current_file, $level + 1, $index, $size ); ?>
					</ul>
				</li>
			<?php
			endforeach;
		} else {
			$url = add_query_arg( array( 'vsp-log-file' => md5( $tree ) ) );
			?>
			<li role="none" class="<?php echo esc_attr( $current_file === $tree ? 'current-file' : '' ); ?>">
				<a role="treeitem" tabindex="<?php echo esc_attr( $current_file === $tree ? '0' : '-1' ); ?>"
				   href="<?php echo esc_url( $url ); ?>"
				   aria-level="<?php echo esc_attr( $level ); ?>"
				   aria-setsize="<?php echo esc_attr( $size ); ?>"
				   aria-posinset="<?php echo esc_attr( $index ); ?>">
					<?php
					if ( $current_file === $tree ) {
						echo '<span class="notice notice-info">' . esc_html( $label ) . '</span>';
					} else {
						echo esc_html( $label );
					}
					?>
				</a>
			</li>
			<?php
		}
	}
}

if ( ! function_exists( 'vsp_list_log_files' ) ) {
	/**
	 * @param bool $path
	 *
	 * @return bool|string[]
	 */
	function vsp_list_log_files( $path = false ) {
		if ( false === $path ) {
			$custom_path = false;
			$path        = VSP_LOG_DIR;
		} else {
			$custom_path = $path;
			$path        = VSP_LOG_DIR . $path;
		}

		$paths = vsp_list_files( $path, 1000 );

		foreach ( $paths as $i => $_path ) {
			$paths[ $i ] = ltrim( vsp_censor_path( $_path, $path ), '/' );
			if ( false !== $custom_path ) {
				$paths[ $i ] = vsp_censor_path( $custom_path ) . '/' . $paths[ $i ];
			}
		}
		return array_values( array_unique( $paths ) );
	}
}

if ( ! function_exists( 'vsp_make_log_list_tree' ) ) {
	function vsp_make_log_list_tree( $logs ) {
		$tree_list = array();
		foreach ( $logs as $log ) {
			$files    = explode( '/', $log );
			$last_dir = &$tree_list;
			foreach ( $files as $dir ) {
				$last_dir =& $last_dir[ $dir ];
			}
			$last_dir = $log;
		}
		return $tree_list;
	}
}