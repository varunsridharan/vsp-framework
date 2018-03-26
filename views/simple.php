<div class="vsp_with_metaboxes">

	<?php
	global $wpsf_submenus;
	$wpsf_submenus = array();
	/**
	 * @param array  $menus
	 * @param string $parent_name
	 */
	function wpsf_simple_render_submenus( $menus = array(), $parent_name = null, $class = array() ) {
		global $wpsf_submenus;
		$return = array();
		$first  = current( $menus );
		$first  = isset( $first['name'] ) ? $first['name'] : false;
		foreach ( $menus as $nav ) {
			if ( isset( $nav['is_separator'] ) && $nav['is_separator'] === true ) {
				continue;
			}
			$title     = ( isset( $nav['title'] ) ) ? $nav['title'] : "";
			$href      = ( isset( $nav['href'] ) && $nav['href'] !== false ) ? $nav['href'] : '#';
			$is_active = ( isset( $nav['is_active'] ) && $nav['is_active'] === true ) ? ' current ' : '';

			if ( empty( $is_active ) ) {
				$is_active = ( $parent_name !== $class->active() && $first === $nav['name'] ) ? 'current' : $is_active;
			}

			$icon     = $class->icon( $nav );
			$return[] = '<li> <a href="' . $href . '" class="' . $is_active . '" data-parent-section="' . $parent_name . '" data-section="' . $nav['name'] . '">' . $icon . ' ' . $title . '</a>';
		}
		$wpsf_submenus[ $parent_name ] = implode( '|</li>', $return );
	}

	if ( ! empty( $title ) ) { ?> <h2><?php echo $title; ?> </h2> <?php } ?>

    <h2 class="nav-tab-wrapper wpsf-main-nav">
		<?php
		foreach ( $class->navs() as $nav ) {
			$title     = ( isset( $nav['title'] ) ) ? $nav['title'] : "";
			$href      = ( isset( $nav['href'] ) && $nav['href'] !== false ) ? $nav['href'] : '#';
			$is_active = ( isset( $nav['is_active'] ) && $nav['is_active'] === true ) ? ' nav-tab-active ' : '';
			if ( isset( $nav['is_separator'] ) && $nav['is_separator'] === true ) {
				continue;
			}
			echo '<a href="' . $href . '" class="nav-tab ' . $is_active . '" data-section="' . $nav['name'] . '">' . $class->icon( $nav ) . ' ' . $title . '</a>';
			if ( isset( $nav['submenus'] ) ) {
				wpsf_simple_render_submenus( $nav['submenus'], $nav['name'], $class );
			}
		}
		?>
    </h2>


    <div id="poststuff">
        <div class="metabox-holder columns-2" id="post-body">
            <div id="post-body-content">
                <div class="wpsf-content">
                    <div class="wpsf-sections">
						<?php
						foreach ( $class->options as $option ) {
							if ( $single_page === 'no' && $option['name'] !== $class->active() ) {
								continue;
							}
							if ( ! isset( $option['fields'] ) && ! isset( $option['callback_hook'] ) && ! isset( $option['sections'] ) ) {
								continue;
							}

							$is_active_page = ( $class->active() === $option['name'] ) ? '' : 'style="display:none;"';

							$pg_active = ( $option['name'] === $class->active() ) ? true : false;
							echo '<div id="wpsf-tab-' . $option['name'] . '" ' . $is_active_page . '>';
							echo '<div class="postbox">';
							if ( isset( $wpsf_submenus[ $option['name'] ] ) && ! empty( $wpsf_submenus[ $option['name'] ] ) ) {
								echo '<h2 class="wpsf-subnav-container hndle">';
								echo '<ul class="wpsf-submenus subsubsub"  id="wpsf-tab-' . $option['name'] . '" >' . $wpsf_submenus[ $option['name'] ] . '</ul>';
								echo '</h2>';
							}

							if ( isset( $option['sections'] ) ) {
								echo '<div class="inside">';
								foreach ( $option['sections'] as $section ) {
									$sc_active    = ( $pg_active === true && $section['name'] === $class->active( false ) ) ? true : false;
									$fields       = $class->render_fields( $section );
									$is_sc_active = ( empty( $class->is( 'page_active', $sc_active ) ) ) ? 'style="display:none"' : $class->is( 'page_active', $sc_active );
									echo '<div ' . $is_sc_active . ' id="wpsf-tab-' . $option['name'] . '-' . $section['name'] . '" >';
									echo $class->get_title( $section ) . $fields . '</div>';
								}
								echo '</div>';
							} elseif ( isset( $option['fields'] ) ) {
								$fields = $class->render_fields( $option );
								echo '<div class="inside">' . $class->get_title( $option ) . $fields . '</div>';
							} elseif ( isset( $option['callback_hook'] ) ) {
								$fields  = $class->render_fields( $option );
								$is_wrap = ( isset( $option['with_wrapper'] ) && $option['with_wrapper'] === true ) ? true : false;

								if ( $is_wrap ) {
									echo '<div class="inside">' . $class->get_title( $option ) . $fields . '</div>';
								} else {
									echo $fields;
								}

							}

							echo '</div>';
							echo '</div>';
						}

						?>
                    </div>
                    <div class="wpsf-sections">
                        <div class="wpsf-simple-footer">
							<?php
							if ( $ajax === 'yes' ) {
								echo '<span id="wpsf-save-ajax">' . esc_html__( "Settings Saved", 'wpsf-framework', 'vsp-framework' ) . '</span>';
							}
							echo $class->get_settings_buttons(); ?>
                        </div>
                    </div>
                </div>
            </div>

			<?php echo do_action( "vsp_wp_settings_simple_footer" ); ?>
        </div>
    </div>
</div>