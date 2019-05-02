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
			$text_domain = $plugin_data['TextDomain'];
			if ( $text_domain ) {
				if ( ! is_textdomain_loaded( $text_domain ) ) {
					if ( $plugin_data['DomainPath'] ) {
						load_plugin_textdomain( $text_domain, false, dirname( $plugin_file ) . $plugin_data['DomainPath'] );
					} else {
						load_plugin_textdomain( $text_domain, false, dirname( $plugin_file ) );
					}
				}
			} elseif ( 'hello.php' == basename( $plugin_file ) ) {
				$text_domain = 'default';
			}
			if ( $text_domain ) {
				foreach ( array( 'Name', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version' ) as $field ) {
					$plugin_data[ $field ] = translate( $plugin_data[ $field ], $text_domain );
				}
			}
		}

		// Sanitize fields
		$allowed_tags_in_links = array(
			'abbr'    => array( 'title' => true ),
			'acronym' => array( 'title' => true ),
			'code'    => true,
			'em'      => true,
			'strong'  => true,
		);
		$allowed_tags          = $allowed_tags_in_links;
		$allowed_tags['a']     = array(
			'href'  => true,
			'title' => true,
		);

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
			if ( $plugin_data['PluginURI'] && $plugin_data['Name'] ) {
				$plugin_data['Title'] = '<a href="' . $plugin_data['PluginURI'] . '">' . $plugin_data['Name'] . '</a>';
			}

			if ( $plugin_data['AuthorURI'] && $plugin_data['Author'] ) {
				$plugin_data['Author'] = '<a href="' . $plugin_data['AuthorURI'] . '">' . $plugin_data['Author'] . '</a>';
			}

			$plugin_data['Description'] = wptexturize( $plugin_data['Description'] );

			if ( $plugin_data['Author'] ) {
				$plugin_data['Description'] .= ' <cite>' . sprintf( __( 'By %s.', 'vsp-framework' ), $plugin_data['Author'] ) . '</cite>';
			}
		}

		return $plugin_data;
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

		if ( false === $is_addon ) {
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
