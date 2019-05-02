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
		if ( ! function_exists( '_get_plugin_data_markup_translate' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return _get_plugin_data_markup_translate( $plugin_file, $plugin_data, $markup, $translate );
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
		function vsp_get_shortcode_regex( $tagnames = null, $is_addon = false ) {
			global $shortcode_tags;
			$tagnames = empty( $tagnames ) ? array_keys( $shortcode_tags ) : $tagnames;
			$tagnames = ( false === $is_addon ) ? array_map( 'preg_quote', $tagnames ) : $tagnames;
			$rx       = join( '|', $tagnames );
			return '\\[(\\[?)(' . $rx . ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
		}
	}
}
