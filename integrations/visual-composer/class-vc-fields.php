<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 19-02-2018
 * Time: 11:44 AM
 */

/**
 * Class VSP_VC_Fields
 *
 * @method set_tag
 * @method set_font_size
 * @method set_font_style_italic
 * @method set_font_style_bold
 * @method set_font_family
 * @method set_color
 * @method set_line_height
 * @method set_text_align
 * @method set_icon_listing
 * @method set_all_icons
 * @method set_icon_types
 * @method set_checkbox
 * @method set_textarea
 * @method set_colorfield
 * @method set_textfield
 * @method set_element_class
 * @method set_element_id
 * @method set_vc_link
 * @method set_font_fields
 * @method set_image_field
 * @method set_css_animations
 * @method set_text_source
 * @method set_dropdown
 * @method set_use_theme_fonts
 * @method set_google_titles
 * @method set_google_fonts
 */
class VSP_VC_Fields {

	public function __construct() {
		$this->icon_list_field       = 'type';
		$this->icon_settings         = array();
		$this->form_group            = '';
		$this->font_container        = array();
		$this->settings_array        = array();
		$this->default_google_titles = array(
			'font_family' => __( "Select Font Family" ),
			'font_style'  => __( "Select font styling" ),
		);
		$this->google_titles         = array(
			'font_family' => __( "Select Font Family" ),
			'font_style'  => __( "Select font styling" ),
		);
	}

	public function __call( $name, $arguments ) {
		$func_name = str_replace( "set_", '', $name );
		if ( method_exists( $this, $func_name ) ) {
			$data = call_user_func_array( array( $this, $func_name ), $arguments );
			$this->add_setting( $data );
		}
		return $this;
	}

	public function add_setting( $settings, $force = false ) {
		if ( ! empty( $settings ) && ! is_object( $settings ) ) {
			if ( $force ) {
				$this->settings_array = $settings;
			} else {
				$this->settings_array[] = $settings;
			}
		}
	}

	public function clear_settings() {
		$this->clear_group();
		$this->settings_array = array();
		return $this;
	}

	public function clear_group() {
		$this->form_group = '';
		return $this;
	}

	public function set_group( $title ) {
		$this->form_group = $title;
		return $this;
	}

	public function tag( $title = '' ) {
		$this->font_container[]                  = 'tag';
		$this->font_container['tag_description'] = empty( $title ) ? __( 'Select element tag.' ) : $title;
		return $this;
	}

	public function font_size( $title = '' ) {
		$this->font_container[]                        = 'font_size';
		$this->font_container['font_size_description'] = empty( $title ) ? __( 'Enter font size' ) : $title;
		return $this;
	}

	public function font_style_italic( $title = '' ) {
		$this->font_container[]                                = 'font_style_italic';
		$this->font_container['font_style_italic_description'] = empty( $title ) ? __( 'Italic' ) : $title;
		return $this;
	}

	public function font_style_bold( $title = '' ) {
		$this->font_container[]                              = 'font_style_bold';
		$this->font_container['font_style_bold_description'] = empty( $title ) ? __( 'Bold' ) : $title;
		return $this;
	}

	public function font_family( $title = '' ) {
		$this->font_container[]                          = 'font_family';
		$this->font_container['font_family_description'] = empty( $title ) ? __( 'Font Family' ) : $title;
		return $this;
	}

	public function color( $title = '' ) {
		$this->font_container[]                    = 'color';
		$this->font_container['color_description'] = empty( $title ) ? __( 'Select font color.' ) : $title;
		return $this;
	}

	public function line_height( $title = '' ) {
		$this->font_container[]                          = 'line_height';
		$this->font_container['line_height_description'] = empty( $title ) ? __( 'Enter line height.' ) : $title;
		return $this;
	}

	public function text_align( $title = '' ) {
		$this->font_container[]                         = 'text_align';
		$this->font_container['text_align_description'] = empty( $title ) ? __( 'Select text alignment' ) : $title;
		return $this;
	}

	public function settings_section( $array = array() ) {
		return $this->_merge_data( $array, array(
			'description'             => __( 'Features Box' ),
			'name'                    => __( 'Features Box' ),
			'base'                    => 'clarup_features_box',
			'icon'                    => 'icon-clarup-features-box',
			'category'                => __( 'ClarUP' ),
			'show_settings_on_create' => true,
			'params'                  => $this->get_settings_fields(),
		) );
	}

	public function _merge_data( $new_array, $default ) {
		if ( empty( $new_array ) ) {
			return $default;
		}
		return wp_parse_args( $new_array, $default );
	}

	public function get_settings_fields() {
		return $this->settings_array;
	}

	public function merge_settings_fields( $new_array = array() ) {
		$this->settings_array = array_merge( $this->settings_array, $new_array );
		return $this;
	}

	public function icon_listing( $array = array() ) {
		$icon_prefix = isset( $array['icon_prefix'] ) ? $array['icon_prefix'] : '';
		unset( $array['icon_prefix'] );
		$data = $this->_merge( $array, array(
			'type'        => 'dropdown',
			'heading'     => __( 'Icon library' ),
			'value'       => $this->get_icons( true ),
			'param_name'  => 'type',
			'description' => __( 'Select icon library.' ),
		) );

		if ( ! empty( $icon_prefix ) ) {
			$this->icon_list_filed = $icon_prefix;
		} else {
			$this->icon_list_field = $data['param_name'];
		}
		return $data;
	}

	public function _merge( $new_array, $default ) {
		$new_data = $this->_merge_data( $new_array, $default );
		if ( ! empty( $new_data ) ) {
			if ( ! empty( $this->get_group() ) ) {
				$new_data['group'] = $this->get_group();
			}
		}

		return $new_data;
	}

	public function get_group() {
		return $this->form_group;
	}

	public function get_icons( $is_options = false ) {
		if ( empty( $this->icon_settings ) ) {
			$this->icon_settings = apply_filters( 'vsp_vc_icon_types', array(
				'fontawesome' => array(
					'value'    => 'fa fa-adjust',
					'settings' => array( 'emptyIcon' => false, 'iconsPerPage' => 4000, ),
					'name'     => __( 'Font Awesome' ),
				),
				'openiconic'  => array(
					'value'    => 'vc-oi vc-oi-dial',
					'settings' => array( 'emptyIcon' => false, 'type' => 'openiconic', 'iconsPerPage' => 4000, ),
					'name'     => __( 'Open Iconic' ),
				),
				'typicons'    => array(
					'value'    => 'typcn typcn-adjust-brightness',
					'settings' => array( 'emptyIcon' => false, 'type' => 'typicons', 'iconsPerPage' => 4000, ),
					'name'     => __( 'Typicons' ),
				),
				'entypo'      => array(
					'value'    => 'entypo-icon entypo-icon-note',
					'name'     => __( 'Entypo' ),
					'settings' => array( 'emptyIcon' => false, 'type' => 'entypo', 'iconsPerPage' => 4000, ),
				),
				'linecons'    => array(
					'value'    => 'vc_li vc_li-heart',
					'name'     => __( 'Linecons' ),
					'settings' => array( 'emptyIcon' => false, 'type' => 'linecons', 'iconsPerPage' => 4000, ),
				),
				'monosocial'  => array(
					'value'    => 'vc-mono vc-mono-fivehundredpx',
					'name'     => __( 'Mono Social' ),
					'settings' => array( 'emptyIcon' => false, 'type' => 'monosocial', 'iconsPerPage' => 4000, ),
				),
				'material'    => array(
					'value'    => 'vc-material vc-material-cake',
					'name'     => __( 'Material' ),
					'settings' => array( 'emptyIcon' => false, 'type' => 'material', 'iconsPerPage' => 4000, ),
				),
			) );
		}

		if ( $is_options === true ) {
			$r = array();
			foreach ( $this->icon_settings as $key => $val ) {
				$r[ $val['name'] ] = $key;
			}
			return $r;
		}

		return $this->icon_settings;

	}

	public function get_icon_types() {
		$types = array_values( $this->get_icons( true ) );
		foreach ( $types as $type => $t ) {
			$types[ $type ] = 'icon_' . $t;
		}

		return $types;
	}

	public function all_icons( $args = array() ) {
		if ( is_array( $this->get_icons() ) ) {
			foreach ( array_keys( $this->get_icons() ) as $icon ) {
				$this->set_icon_types( $icon, $args );
			}
		}
		return '';
	}

	public function icon_types( $icon_type = '', $args = array() ) {
		$defaults = array(
			'type'        => 'iconpicker',
			'heading'     => __( 'Icon' ),
			'param_name'  => 'icon_' . $icon_type,
			'value'       => $this->get_icon_value( $icon_type, 'value', $args ),
			'settings'    => $this->get_icon_value( $icon_type, 'settings', $args ),
			'dependency'  => array( 'element' => $this->icon_list_field, 'value' => $icon_type, ),
			'description' => __( 'Select icon from library.' ),
		);

		return $this->_merge( $args, $defaults );
	}

	private function get_icon_value( $type, $key, $args = array() ) {
		$defined = $this->get_icons();

		if ( isset( $defined[ $type ] ) ) {
			if ( isset( $defined[ $type ][ $key ] ) ) {
				return $defined[ $type ][ $key ];
			}
		}

		if ( isset( $args[ $key ] ) ) {
			return $args[ $key ];
		}

		$final = array( 'settings' => array(), 'value' => '' );
		return isset( $final[ $key ] ) ? $final[ $key ] : "";
	}

	public function textarea( $args = array() ) {
		return $this->_merge( $args, array(
			'type'       => 'textarea',
			'heading'    => __( 'Heading2' ),
			'param_name' => 'feature_text',
		) );
	}

	public function colorfield( $args = array() ) {
		$args = $this->_merge( $args, array( 'type' => 'colorpicker' ) );
		return $this->textfield( $args );
	}

	public function textfield( $args = array() ) {
		return $this->_merge( $args, array(
			'type'       => 'textfield',
			'heading'    => __( 'Extra class name' ),
			'param_name' => 'el_class',
		) );
	}


	public function field( $args = array() ) {
		return $this->_merge( $args, array(
			'type'       => '',
			'param_name' => '',
		) );
	}

	public function element_class( $args = array() ) {
		$args = $this->_merge( $args, array(
			'heading'     => __( "Extra Class Name" ),
			'param_name'  => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.' ),
		) );
		return $this->textfield( $args );
	}

	public function element_id( $args = array() ) {
		return $this->_merge( $args, array(
			'type'        => 'el_id',
			'heading'     => __( 'Element ID' ),
			'param_name'  => 'el_id',
			'description' => sprintf( __( 'Enter element ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
		) );
	}

	public function vc_link( $args = array() ) {
		return $this->_merge( $args, array(
			'type'        => 'vc_link',
			'heading'     => __( 'URL (Link)' ),
			'param_name'  => 'link',
			'description' => __( 'Add link to custom heading.' ),
		) );
	}

	public function font_fields( $args = array() ) {
		$defaults = array(
			'type'       => 'font_container',
			'param_name' => 'font_container',
			'value'      => 'tag:h2|text_align:left',
			'settings'   => array(
				'fields' => $this->get_font_container(),
			),
		);

		$this->clear_font_container();
		return $this->_merge( $args, $defaults );
	}

	public function get_font_container() {
		return $this->font_container;
	}

	public function clear_font_container() {
		$this->font_container = array();
		return $this;
	}

	public function image_field( $args = array() ) {
		return $this->_merge( $args, array(
			'type'        => 'attach_image',
			'heading'     => __( 'Image' ),
			'param_name'  => 'image',
			'description' => __( 'Image Size must be at 210px by 210px.' ),
		) );
	}

	public function css_animations( $args = array() ) {
		return $this->_merge( $args, vc_map_add_css_animation() );
	}

	public function text_source( $args = array() ) {
		$defaults = array( 'heading' => __( "Text source" ) );
		$args     = $this->_merge( $args, $defaults );
		return $this->dropdown( $args, array(
			__( 'Custom text' )        => '',
			__( 'Post or Page Title' ) => 'post_title',
		) );
	}

	public function dropdown( $args = array(), $values = array() ) {
		return $this->_merge( $args, array(
			'type'        => 'dropdown',
			'heading'     => __( 'Text source' ),
			'param_name'  => 'source',
			'value'       => $values,
			'std'         => '',
			'description' => __( 'Select text source.' ),
		) );
	}

	public function use_theme_fonts( $args = array() ) {
		return $this->checkbox( $this->_merge( $args, array(
			'heading'    => __( "Use theme default font family?" ),
			'param_name' => 'use_theme_fonts',
		) ) );
	}

	public function checkbox( $args = array() ) {
		return $this->_merge( $args, array(
			'type'        => 'checkbox',
			'heading'     => __( 'Use theme default font family?' ),
			'param_name'  => 'use_theme_fonts',
			'description' => __( 'Use font family from the theme.' ),
			'value'       => array( __( 'Yes' ) => 'yes' ),
		) );
	}

	public function google_titles( $args = array() ) {
		$this->google_titles = $this->_merge( $args, $this->default_google_titles );
		return array();
	}

	public function google_fonts( $args = array() ) {
		return $this->_merge( $args, array(
			'type'       => 'google_fonts',
			'param_name' => 'google_fonts',
			'value'      => '',
			'settings'   => array(
				'fields' => array(
					'font_family_description' => $this->google_titles['font_family'],
					'font_style_description'  => $this->google_titles['font_style'],
				),
			),
			'dependency' => array(
				'element'            => 'use_theme_fonts',
				'value_not_equal_to' => 'yes',
			),
		) );
	}
}
