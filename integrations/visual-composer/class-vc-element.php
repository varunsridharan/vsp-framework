<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 19-02-2018
 * Time: 02:41 PM
 *
 * @author     Varun Sridharan <varunsridharan23@gmail.com>
 * @since      1.0
 * @package    vsp-framework
 * @subpackage integrations/visual-composer
 * @copyright  GPL V3 Or greater
 */

/**
 * Trait VSP_VC_Element
 *
 * @use WPBakeryShortCode
 */
trait VSP_VC_Element {
	/**
	 * Atts
	 *
	 * @var array
	 */
	public $_atts = array();

	/**
	 * @param $atts
	 *
	 * @return array
	 */
	public function shortcode_atts( $atts ) {
		$this->_atts = wp_parse_args( vc_map_get_attributes( $this->getShortcode(), $atts ), $this->shortcode_defaults() );
		return $this->_atts;
	}

	/**
	 * Returns Shortcode Defaults
	 *
	 * @return mixed
	 */
	public function shortcode_defaults() {
		return vc_map_get_params_defaults( $this->settings['params'] );
	}

	/**
	 * Checks if google font is used.
	 *
	 * @param bool  $is_use .
	 * @param array $fonts .
	 *
	 * @return bool
	 */
	public function is_use_gfonts( $is_use = false, $fonts = array() ) {
		if ( false === $is_use || empty( $is_use ) ) {
			return false;
		}
		$settings = get_option( 'wpb_js_google_fonts_subsets' );
		if ( is_array( $settings ) && ! empty( $settings ) ) {
			$subsets = '&subset=' . implode( ',', $settings );
		} else {
			$subsets = '';
		}

		if ( isset( $fonts['values']['font_family'] ) ) {
			wp_enqueue_style( 'vc_google_fonts_' . vc_build_safe_css_class( $fonts['values']['font_family'] ), '//fonts.googleapis.com/css?family=' . $fonts['values']['font_family'] . $subsets );
		}
		return false;
	}

	/**
	 * Extracts Fonts Data.
	 *
	 * @param array $atts .
	 * @param array $options .
	 *
	 * @return array
	 */
	public function font_data( $atts, $options = array() ) {
		$options     = wp_parse_args( $options, array(
			'google_fonts'   => 'google_fonts',
			'font_container' => 'font_container',
		) );
		$this->_atts = $atts;

		$font_container       = $this->option_value( $options['font_container'], null );
		$google_fonts         = $this->option_value( $options['google_fonts'], null );
		$google_fonts_field   = $this->getParamData( 'google_fonts' );
		$font_container_field = $this->getParamData( 'font_container' );

		$font_container_obj = new Vc_Font_Container();
		$google_fonts_obj   = new Vc_Google_Fonts();

		$font_container_field_settings = isset( $font_container_field['settings'], $font_container_field['settings']['fields'] ) ? $font_container_field['settings']['fields'] : array();
		$google_fonts_field_settings   = isset( $google_fonts_field['settings'], $google_fonts_field['settings']['fields'] ) ? $google_fonts_field['settings']['fields'] : array();

		$font_container_data = $font_container_obj->_vc_font_container_parse_attributes( $font_container_field_settings, $font_container );
		$google_fonts_data   = strlen( $google_fonts ) > 0 ? $google_fonts_obj->_vc_google_fonts_parse_attributes( $google_fonts_field_settings, $google_fonts ) : '';

		return array(
			'google_fonts'        => $google_fonts,
			'font_container'      => $font_container,
			'font_container_data' => $font_container_data,
			'google_fonts_data'   => $google_fonts_data,
		);
	}

	/**
	 * Checks if given key exists in $this->_atts
	 *
	 * @param string $key .
	 * @param mixed  $default .
	 *
	 * @return string
	 */
	private function option_value( $key, $default = '' ) {
		if ( isset( $this->_atts[ $key ] ) ) {
			return $this->_atts[ $key ];
		}
		return $default;
	}

	/**
	 * Returns ParamData from WPBMap::getParam.
	 *
	 * @param string $key .
	 *
	 * @return mixed
	 */
	protected function getParamData( $key ) {
		return WPBMap::getParam( $this->shortcode, $key );
	}

	/**
	 * Extracts And Returns Formatted Element Style
	 *
	 * @param string $el_class .
	 * @param string $css .
	 * @param array  $google_fonts_data .
	 * @param array  $font_container_data .
	 * @param array  $atts .
	 *
	 * @return array
	 */
	public function get_element_style( $el_class = '', $css = '', $google_fonts_data = array(), $font_container_data = array(), $atts = array() ) {
		$args               = $this->getStyles( $el_class, $css, $google_fonts_data, $font_container_data, $atts );
		$args['raw_styles'] = $args['styles'];
		$args['styles']     = esc_attr( implode( ';', $args['styles'] ) );
		return $args;
	}

	/**
	 * Returns Elements Style.
	 *
	 * @param string $el_class .
	 * @param string $css .
	 * @param array  $font_data .
	 * @param bool   $use_theme_fonts .
	 *
	 * @return array
	 */
	public function getStyles( $el_class = '', $css = '', $font_data = array(), $use_theme_fonts = false ) {
		$styles   = array();
		$el_class = $this->getExtraClass( $el_class );

		$font_container_data = isset( $font_data['font_container_data'] ) ? $font_data['font_container_data'] : array();
		$gfonts              = isset( $font_data['google_fonts_data'] ) ? $font_data['google_fonts_data'] : array();

		if ( ! empty( $font_container_data ) && isset( $font_container_data['values'] ) ) {
			foreach ( $font_container_data['values'] as $key => $value ) {
				if ( 'tag' !== $key && strlen( $value ) ) {
					if ( preg_match( '/description/', $key ) ) {
						continue;
					}
					if ( 'font_size' === $key || 'line_height' === $key || 'letter_spacing' === $key ) {
						$value = preg_replace( '/\s+/', '', $value );
					}
					if ( 'font_size' === $key ) {
						$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
						// allowed metrics: http://www.w3schools.com/cssref/css_units.asp
						$regexr = preg_match( $pattern, $value, $matches );
						$value  = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
						$unit   = isset( $matches[2] ) ? $matches[2] : 'px';
						$value  = $value . $unit;
					}
					if ( strlen( $value ) > 0 ) {
						$styles[] = str_replace( '_', '-', $key ) . ': ' . $value;
					}
				}
			}
		}

		if ( ( false === $use_theme_fonts || empty( $use_theme_fonts ) ) && ! empty( $gfonts ) && isset( $gfonts['values'], $gfonts['values']['font_family'], $gfonts['values']['font_style'] ) ) {
			$google_fonts_family = explode( ':', $gfonts['values']['font_family'] );
			$styles[]            = 'font-family:' . $google_fonts_family[0];
			$google_fonts_styles = explode( ':', $gfonts['values']['font_style'] );
			$styles[]            = 'font-weight:' . $google_fonts_styles[1];
			$styles[]            = 'font-style:' . $google_fonts_styles[2];
		}


		//@todo check for custom class option
		//$css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'booss_styled_heading ' . $el_class . vc_shortcode_custom_css_class($css, ' '), $this->settings['base'], $atts);
		$css_class = $el_class . vc_shortcode_custom_css_class( $css, ' ' );

		return array(
			'css_class' => trim( preg_replace( '/\s+/', ' ', $css_class ) ),
			'styles'    => $styles,
		);
	}

	/**
	 * Returns Elements Links With Custom text
	 *
	 * @param string $key .
	 * @param string $text .
	 *
	 * @return string
	 */
	public function get_link( $key, $text = '' ) {
		if ( empty( $this->_atts[ $key ] ) ) {
			return $text;
		}
		$link = vc_build_link( $this->_atts[ $key ] );
		$link = array_map( 'esc_attr', $link );
		$r    = '<a href="' . $link['url'] . '" ';
		if ( isset( $link['target'] ) && ! empty( $link['target'] ) ) {
			$r .= ' target="' . $link['target'] . '" ';
		}

		if ( isset( $link['rel'] ) && ! empty( $link['rel'] ) ) {
			$r .= ' rel="' . $link['rel'] . '" ';
		}

		if ( isset( $link['title'] ) && ! empty( $link['title'] ) ) {
			$r .= ' title="' . $link['title'] . '" ';
		}

		return $r . '>' . $text . '</a>';
	}

	/**
	 * Extracts Args
	 *
	 * @param bool   $shortcode .
	 * @param array  $args .
	 * @param string $prefix .
	 *
	 * @return array
	 */
	public function extract_args( $shortcode = false, $args = array(), $prefix = '' ) {
		if ( false === $shortcode ) {
			$return = array();

			foreach ( $args as $key => $a ) {
				if ( strpos( $key, $prefix ) !== false ) {
					$return[ $key ] = $a;
				}
			}
			return $return;
		} else {
			return vc_map_integrate_parse_atts( $this->shortcode, $shortcode, $args, $prefix );
		}

	}

	public function vsp_do_shortcodes( $shortcode_name = '', $atts = array(), $content = null ) {
		var_dump( $content );
	}

	/**
	 * Renders Shortcode.
	 *
	 * @param string $shortcode_name .
	 * @param array  $atts .
	 * @param null   $content .
	 *
	 * @return bool
	 */
	public function do_shortcode( $shortcode_name = '', $atts = array(), $content = null ) {
		$custom_heading = visual_composer()->getShortCode( $shortcode_name );
		if ( $custom_heading ) {
			return $custom_heading->render( array_filter( $atts ), $content );
		}
		return false;
	}

}
