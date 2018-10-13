<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

if ( ! function_exists( 'vsp_addons_extract_tags' ) ) {
	/**
	 * Extracts Addon Tags
	 *
	 * @param string $content .
	 * @param bool   $is_addons_reqplugin .
	 *
	 * preg_match_all( '@\[([^<>&\[\]\x00-\x20=]++)@',$content, $reg_shortcodes ).
	 *
	 * @return mixed
	 */
	function vsp_addons_extract_tags( $content, $is_addons_reqplugin = false ) {
		$pattern = ( false === $is_addons_reqplugin ) ? '@\[([^<>&/\[\]\x00-\x20=]++)@' : '@\[(\w[^<>&\[\]\x00-\x20=]++)@';
		preg_match_all( $pattern, $content, $reg_shortcodes );
		return $reg_shortcodes;
	}
}

if ( ! function_exists( 'vsp_addons_extract_tags_pattern' ) ) {
	/**
	 * Extract Tags
	 *
	 * @param array|string $tags .
	 * @param string       $content .
	 * @param bool         $is_addon .
	 *
	 * @return mixed
	 */
	function vsp_addons_extract_tags_pattern( $tags, $content, $is_addon = false ) {
		if ( ! is_array( $tags ) ) {
			$tags = array( $tags );
		}

		foreach ( $tags as $i => $tag ) {
			$tags[ $i ] = str_replace( '/', '\/', $tag );
		}

		$patterns = vsp_get_shortcode_regex( $tags, $is_addon );
		preg_match( "/$patterns/", $content, $data );
		return $data;
	}
}

if ( ! function_exists( 'vsp_cdn_url' ) ) {
	/**
	 * Returns CDN URL
	 *
	 * @return string
	 */
	function vsp_cdn_url() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			return 'https://varunsridharan.github.io/vs-plugins-cdn-dev/';
		} else {
			return 'https://varunsridharan.github.io/vs-plugins-cdn/';
		}
	}
}

if ( ! function_exists( 'vsp_get_cdn' ) ) {
	/**
	 * Gets CDN Data.
	 *
	 * @param string $part_url .
	 * @param bool   $force_decode .
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	function vsp_get_cdn( $part_url, $force_decode = false ) {
		$part_url = ltrim( $part_url, '/' );
		$url      = vsp_cdn_url() . $part_url;
		$resource = wp_remote_get( $url );

		if ( is_wp_error( $resource ) ) {
			return $resource;
		} else {
			$body = wp_remote_retrieve_body( $resource );
			return json_decode( $body, $force_decode );
		}
	}
}

if ( ! function_exists( 'vsp_addon_data_markup' ) ) {
	/**
	 * @param      $plugin_file .
	 * @param      $plugin_data .
	 * @param bool $markup .
	 * @param bool $translate .
	 *
	 * @return mixed
	 */
	function vsp_addon_data_markup( $plugin_file, $plugin_data, $markup = true, $translate = true ) {
		if ( function_exists( '_get_plugin_data_markup_translate' ) ) {
			return _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
		}

		// Sanitize the plugin filename to a WP_PLUGIN_DIR relative path
		$plugin_file = plugin_basename( $plugin_file );

		// Translate fields
		if ( $translate ) {
			if ( $textdomain = $plugin_data['TextDomain'] ) {
				if ( ! is_textdomain_loaded( $textdomain ) ) {
					if ( $plugin_data['DomainPath'] ) {
						load_plugin_textdomain( $textdomain, false, dirname( $plugin_file ) . $plugin_data['DomainPath'] );
					} else {
						load_plugin_textdomain( $textdomain, false, dirname( $plugin_file ) );
					}
				}
			} elseif ( 'hello.php' == basename( $plugin_file ) ) {
				$textdomain = 'default';
			}
			if ( $textdomain ) {
				foreach ( array( 'Name', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version' ) as $field ) {
					$plugin_data[ $field ] = translate( $plugin_data[ $field ], $textdomain );
				}
			}
		}

		// Sanitize fields
		$allowed_tags      = $allowed_tags_in_links = array(
			'abbr'    => array( 'title' => true ),
			'acronym' => array( 'title' => true ),
			'code'    => true,
			'em'      => true,
			'strong'  => true,
		);
		$allowed_tags['a'] = array( 'href' => true, 'title' => true );

		// Name is marked up inside <a> tags. Don't allow these.
		// Author is too, but some plugins have used <a> here (omitting Author URI).
		$plugin_data['Name']        = wp_kses( $plugin_data['Name'], $allowed_tags_in_links );
		$plugin_data['Author']      = wp_kses( $plugin_data['Author'], $allowed_tags );
		$plugin_data['Description'] = wp_kses( $plugin_data['Description'], $allowed_tags );
		$plugin_data['Version']     = wp_kses( $plugin_data['Version'], $allowed_tags );
		$plugin_data['PluginURI']   = esc_url( $plugin_data['PluginURI'] );
		$plugin_data['AuthorURI']   = esc_url( $plugin_data['AuthorURI'] );
		$plugin_data['Title']       = $plugin_data['Name'];
		$plugin_data['AuthorName']  = $plugin_data['Author'];

		// Apply markup
		if ( $markup ) {
			if ( $plugin_data['PluginURI'] && $plugin_data['Name'] )
				$plugin_data['Title'] = '<a href="' . $plugin_data['PluginURI'] . '">' . $plugin_data['Name'] . '</a>';

			if ( $plugin_data['AuthorURI'] && $plugin_data['Author'] )
				$plugin_data['Author'] = '<a href="' . $plugin_data['AuthorURI'] . '">' . $plugin_data['Author'] . '</a>';

			$plugin_data['Description'] = wptexturize( $plugin_data['Description'] );

			if ( $plugin_data['Author'] )
				$plugin_data['Description'] .= ' <cite>' . sprintf( __( 'By %s.', 'vsp-framework' ), $plugin_data['Author'] ) . '</cite>';
		}

		return $plugin_data;
	}
}

if ( ! function_exists( 'vsp_addon_information' ) ) {
	/**
	 * Display plugin information in dialog box form.
	 *
	 * @since 2.7.0
	 *
	 * @global string $tab
	 */
	function vsp_addon_information() {
		global $tab;

		if ( empty( $_REQUEST['plugin'] ) ) {
			return;
		}

		$api = plugins_api( 'plugin_information', array(
			'slug'   => wp_unslash( $_REQUEST['plugin'] ),
			'is_ssl' => is_ssl(),
			'fields' => array(
				'banners'         => true,
				'reviews'         => true,
				'downloaded'      => false,
				'active_installs' => true,
			),
		) );

		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$plugins_allowedtags = array(
			'a'          => array( 'href' => array(), 'title' => array(), 'target' => array() ),
			'abbr'       => array( 'title' => array() ),
			'acronym'    => array( 'title' => array() ),
			'code'       => array(),
			'pre'        => array(),
			'em'         => array(),
			'strong'     => array(),
			'div'        => array( 'class' => array() ),
			'span'       => array( 'class' => array() ),
			'p'          => array(),
			'br'         => array(),
			'ul'         => array(),
			'ol'         => array(),
			'li'         => array(),
			'h1'         => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'h5'         => array(),
			'h6'         => array(),
			'img'        => array( 'src' => array(), 'class' => array(), 'alt' => array() ),
			'blockquote' => array( 'cite' => true ),
		);

		$plugins_section_titles = array(
			'description'  => _x( 'Description', 'Plugin installer section title', 'vsp-framework' ),
			'installation' => _x( 'Installation', 'Plugin installer section title', 'vsp-framework' ),
			'faq'          => _x( 'FAQ', 'Plugin installer section title', 'vsp-framework' ),
			'screenshots'  => _x( 'Screenshots', 'Plugin installer section title', 'vsp-framework' ),
			'changelog'    => _x( 'Changelog', 'Plugin installer section title', 'vsp-framework' ),
			'reviews'      => _x( 'Reviews', 'Plugin installer section title', 'vsp-framework' ),
			'other_notes'  => _x( 'Other Notes', 'Plugin installer section title', 'vsp-framework' ),
		);

		// Sanitize HTML
		foreach ( (array) $api->sections as $section_name => $content ) {
			$api->sections[ $section_name ] = wp_kses( $content, $plugins_allowedtags );
		}

		foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
			if ( isset( $api->$key ) ) {
				$api->$key = wp_kses( $api->$key, $plugins_allowedtags );
			}
		}

		$_tab = esc_attr( $tab );

		$section = isset( $_REQUEST['section'] ) ? wp_unslash( $_REQUEST['section'] ) : 'description'; // Default to the Description tab, Do not translate, API returns English.
		if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
			$section_titles = array_keys( (array) $api->sections );
			$section        = reset( $section_titles );
		}

		iframe_header( __( 'Plugin Installation', 'vsp-framework' ) );

		$_with_banner = '';

		if ( ! empty( $api->banners ) && ( ! empty( $api->banners['low'] ) || ! empty( $api->banners['high'] ) ) ) {
			$_with_banner = 'with-banner';
			$low          = empty( $api->banners['low'] ) ? $api->banners['high'] : $api->banners['low'];
			$high         = empty( $api->banners['high'] ) ? $api->banners['low'] : $api->banners['high'];
			?>
			<style type="text/css">
				#plugin-information-title.with-banner {
					background-image: url( <?php echo esc_url( $low ); ?> );
				}

				@media only screen and ( -webkit-min-device-pixel-ratio: 1.5 ) {
					#plugin-information-title.with-banner {
						background-image: url( <?php echo esc_url( $high ); ?> );
					}
				}
			</style>
			<?php
		}

		echo '<div id="plugin-information-scrollable">';
		echo "<div id='{$_tab}-title' class='{$_with_banner}'><div class='vignette'></div><h2>{$api->name}</h2></div>";
		echo "<div id='{$_tab}-tabs' class='{$_with_banner}'>\n";

		foreach ( (array) $api->sections as $section_name => $content ) {
			if ( 'reviews' === $section_name && ( empty( $api->ratings ) || 0 === array_sum( (array) $api->ratings ) ) ) {
				continue;
			}

			if ( isset( $plugins_section_titles[ $section_name ] ) ) {
				$title = $plugins_section_titles[ $section_name ];
			} else {
				$title = ucwords( str_replace( '_', ' ', $section_name ) );
			}

			$class       = ( $section_name === $section ) ? ' class="current"' : '';
			$href        = add_query_arg( array( 'tab' => $tab, 'section' => $section_name ) );
			$href        = esc_url( $href );
			$san_section = esc_attr( $section_name );
			echo "\t<a name='$san_section' href='$href' $class>$title</a>\n";
		}

		echo "</div>\n";

		?>
	<div id="<?php echo $_tab; ?>-content" class='<?php echo $_with_banner; ?>'>
		<div class="fyi">
			<ul>
				<?php if ( ! empty( $api->version ) ) { ?>
					<li><strong><?php _e( 'Version:', 'vsp-framework' ); ?></strong> <?php echo $api->version; ?></li>
				<?php }
				if ( ! empty( $api->author ) ) { ?>
					<li>
						<strong><?php _e( 'Author:', 'vsp-framework' ); ?></strong> <?php echo links_add_target( $api->author, '_blank' ); ?>
					</li>
				<?php }
				if ( ! empty( $api->last_updated ) ) { ?>
					<li><strong><?php _e( 'Last Updated:', 'vsp-framework' ); ?></strong>
						<?php
						/* translators: %s: Time since the last update */
						printf( __( '%s ago', 'vsp-framework' ), human_time_diff( strtotime( $api->last_updated ) ) );
						?>
					</li>
				<?php }
				if ( ! empty( $api->requires ) ) { ?>
					<li>
						<strong><?php _e( 'Requires WordPress Version:', 'vsp-framework' ); ?></strong>
						<?php
						/* translators: %s: WordPress version */
						printf( __( '%s or higher', 'vsp-framework' ), $api->requires );
						?>
					</li>
				<?php }
				if ( ! empty( $api->tested ) ) { ?>
					<li>
						<strong><?php _e( 'Compatible up to:', 'vsp-framework' ); ?></strong> <?php echo $api->tested; ?>
					</li>
				<?php }
				if ( isset( $api->active_installs ) ) { ?>
					<li><strong><?php _e( 'Active Installations:', 'vsp-framework' ); ?></strong> <?php
						if ( $api->active_installs >= 1000000 ) {
							_ex( '1+ Million', 'Active plugin installations', 'vsp-framework' );
						} elseif ( 0 == $api->active_installs ) {
							_ex( 'Less Than 10', 'Active plugin installations', 'vsp-framework' );
						} else {
							echo number_format_i18n( $api->active_installs ) . '+';
						}
						?></li>
				<?php }
				if ( ! empty( $api->slug ) && empty( $api->external ) ) { ?>
					<li><a target="_blank"
						   href="<?php echo __( 'https://wordpress.org/plugins/', 'vsp-framework' ) . $api->slug; ?>/"><?php _e( 'WordPress.org Plugin Page &#187;', 'vsp-framework' ); ?></a>
					</li>
				<?php }
				if ( ! empty( $api->homepage ) ) { ?>
					<li><a target="_blank"
						   href="<?php echo esc_url( $api->homepage ); ?>"><?php _e( 'Plugin Homepage &#187;', 'vsp-framework' ); ?></a>
					</li>
				<?php }
				if ( ! empty( $api->donate_link ) && empty( $api->contributors ) ) { ?>
					<li><a target="_blank"
						   href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;', 'vsp-framework' ); ?></a>
					</li>
				<?php } ?>
			</ul>
			<?php if ( ! empty( $api->rating ) ) { ?>
				<h3><?php _e( 'Average Rating', 'vsp-framework' ); ?></h3>
				<?php wp_star_rating( array(
					'rating' => $api->rating,
					'type'   => 'percent',
					'number' => $api->num_ratings,
				) ); ?>
				<p aria-hidden="true"
				   class="fyi-description"><?php printf( _n( '(based on %s rating)', '(based on %s ratings)', $api->num_ratings, 'vsp-framework' ), number_format_i18n( $api->num_ratings ) ); ?></p>
			<?php }

			if ( ! empty( $api->ratings ) && array_sum( (array) $api->ratings ) > 0 ) { ?>
				<h3><?php _e( 'Reviews', 'vsp-framework' ); ?></h3>
				<p class="fyi-description"><?php _e( 'Read all reviews on WordPress.org or write your own!', 'vsp-framework' ); ?></p>
				<?php
				foreach ( $api->ratings as $key => $ratecount ) {
					// Avoid div-by-zero.
					$_rating = $api->num_ratings ? ( $ratecount / $api->num_ratings ) : 0;
					/* translators: 1: number of stars (used to determine singular/plural), 2: number of reviews */
					$aria_label = esc_attr( sprintf( _n( 'Reviews with %1$d star: %2$s. Opens in a new window.', 'Reviews with %1$d stars: %2$s. Opens in a new window.', $key, 'vsp-framework' ), $key, number_format_i18n( $ratecount ) ) );
					?>
					<div class="counter-container">
						<span class="counter-label"><a
									href="https://wordpress.org/support/plugin/<?php echo $api->slug; ?>/reviews/?filter=<?php echo $key; ?>"
									target="_blank"
									aria-label="<?php echo $aria_label; ?>"><?php printf( _n( '%d star', '%d stars', $key, 'vsp-framework' ), $key ); ?></a></span>
						<span class="counter-back">
							<span class="counter-bar" style="width: <?php echo 92 * $_rating; ?>px;"></span>
						</span>
						<span class="counter-count"
							  aria-hidden="true"><?php echo number_format_i18n( $ratecount ); ?></span>
					</div>
					<?php
				}
			}
			if ( ! empty( $api->contributors ) ) { ?>
				<h3><?php _e( 'Contributors', 'vsp-framework' ); ?></h3>
				<ul class="contributors">
					<?php
					foreach ( (array) $api->contributors as $contrib_username => $contrib_profile ) {
						if ( empty( $contrib_username ) && empty( $contrib_profile ) ) {
							continue;
						}
						if ( empty( $contrib_username ) ) {
							$contrib_username = preg_replace( '/^.+\/(.+)\/?$/', '\1', $contrib_profile );
						}
						$contrib_username = sanitize_user( $contrib_username );
						if ( empty( $contrib_profile ) ) {
							echo "<li><img src='https://wordpress.org/grav-redirect.php?user={$contrib_username}&amp;s=36' width='18' height='18' alt='' />{$contrib_username}</li>";
						} else {
							echo "<li><a href='{$contrib_profile}' target='_blank'><img src='https://wordpress.org/grav-redirect.php?user={$contrib_username}&amp;s=36' width='18' height='18' alt='' />{$contrib_username}</a></li>";
						}
					}
					?>
				</ul>
				<?php if ( ! empty( $api->donate_link ) ) { ?>
					<a target="_blank"
					   href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this plugin &#187;', 'vsp-framework' ); ?></a>
				<?php } ?>
			<?php } ?>
		</div>
		<div id="section-holder" class="wrap">
		<?php
		$wp_version = get_bloginfo( 'version' );

		if ( ! empty( $api->tested ) && version_compare( substr( $wp_version, 0, strlen( $api->tested ) ), $api->tested, '>' ) ) {
			echo '<div class="notice notice-warning notice-alt"><p>' . __( '<strong>Warning:</strong> This plugin has <strong>not been tested</strong> with your current version of WordPress.', 'vsp-framework' ) . '</p></div>';
		} elseif ( ! empty( $api->requires ) && version_compare( substr( $wp_version, 0, strlen( $api->requires ) ), $api->requires, '<' ) ) {
			echo '<div class="notice notice-warning notice-alt"><p>' . __( '<strong>Warning:</strong> This plugin has <strong>not been marked as compatible</strong> with your version of WordPress.', 'vsp-framework' ) . '</p></div>';
		}

		foreach ( (array) $api->sections as $section_name => $content ) {
			$content     = links_add_base_url( $content, 'https://wordpress.org/plugins/' . $api->slug . '/' );
			$content     = links_add_target( $content, '_blank' );
			$san_section = esc_attr( $section_name );
			$display     = ( $section_name === $section ) ? 'block' : 'none';

			echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
			echo $content;
			echo "\t</div>\n";
		}
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n"; // #plugin-information-scrollable
		echo "<div id='$tab-footer'>\n";
		if ( ! empty( $api->download_link ) && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
			$status = install_plugin_install_status( $api );
			switch ( $status['status'] ) {
				case 'install':
					if ( $status['url'] ) {
						echo '<a data-slug="' . esc_attr( $api->slug ) . '" id="plugin_install_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Now', 'vsp-framework' ) . '</a>';
					}
					break;
				case 'update_available':
					if ( $status['url'] ) {
						echo '<a data-slug="' . esc_attr( $api->slug ) . '" data-plugin="' . esc_attr( $status['file'] ) . '" id="plugin_update_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Update Now', 'vsp-framework' ) . '</a>';
					}
					break;
				case 'newer_installed':
					/* translators: %s: Plugin version */
					echo '<a class="button button-primary right disabled">' . sprintf( __( 'Newer Version (%s) Installed', 'vsp-framework' ), $status['version'] ) . '</a>';
					break;
				case 'latest_installed':
					echo '<a class="button button-primary right disabled">' . __( 'Latest Version Installed', 'vsp-framework' ) . '</a>';
					break;
			}
		}
		echo "</div>\n";

		iframe_footer();
		exit;
	}
}

if ( ! function_exists( 'vsp_get_shortcode_regex' ) ) {

	/**
	 * @param null $tagnames
	 * @param bool $is_addon
	 *
	 * @return string
	 */
	function vsp_get_shortcode_regex( $tagnames = null, $is_addon = false ) {
		global $shortcode_tags;

		if ( empty( $tagnames ) ) {
			$tagnames = array_keys( $shortcode_tags );
		}

		if ( $is_addon === false ) {
			$tagnames = array_map( 'preg_quote', $tagnames );
		}

		$tagregexp = join( '|', $tagnames );

		return '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			. '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			. '(?:' . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			. '[^\\]\\/]*'               // Not a closing bracket or forward slash
			. ')*?' . ')' . '(?:' . '(\\/)'                        // 4: Self closing tag ...
			. '\\]'                          // ... and closing bracket
			. '|' . '\\]'                          // Closing bracket
			. '(?:' . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			. '[^\\[]*+'             // Not an opening bracket
			. '(?:' . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			. '[^\\[]*+'         // Not an opening bracket
			. ')*+' . ')' . '\\[\\/\\2\\]'             // Closing shortcode tag
			. ')?' . ')' . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}
}