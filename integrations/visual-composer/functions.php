<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 20-02-2018
 * Time: 01:10 PM
 *
 * @author     Varun Sridharan <varunsridharan23@gmail.com>
 * @since      1.0
 * @package    vsp-framework/integrations/visual-composer
 * @copyright  GPL V3 Or greater
 */


if ( ! function_exists( 'vc_map_shortcode_defaults' ) ) {
	/**
	 * @param array $args
	 * @param array $defaults
	 *
	 * @return array|mixed
	 */
	function vc_map_shortcode_defaults( $args = array(), $defaults = array() ) {
		$is_params = false;
		$loop      = $args;
		if ( isset( $args['params'] ) ) {
			$is_params = true;
			$loop      = $args['params'];
		}

		foreach ( $loop as $i => $el ) {
			if ( isset( $el['param_name'] ) && isset( $defaults[ $el['param_name'] ] ) ) {
				$loop[ $i ] = wp_parse_args( $defaults[ $el['param_name'] ], $el );
			}
		}

		if ( $is_params ) {
			$args['params'] = $loop;
		} else {
			$args = $loop;
		}

		return $args;
	}
}

if ( ! function_exists( 'vsp_vc_render_icon' ) ) {
	/**
	 * @param array $options
	 *
	 * @return string
	 */
	function vsp_vc_render_icon( $options = array() ) {
		$options = wp_parse_args( $options, array(
			'icon_color'        => '',
			'icon_custom_color' => '',
			'icon_size'         => '',
			'icon_el_id'        => '',
			'icon_el_class'     => '',
			'icon'              => '',
			'customize'         => false,
			'attributes'        => array(),
		) );

		$style = '';
		if ( false !== $options['customize'] ) {
			if ( ! empty( $options['icon_color'] ) ) {
				$icon_color = ( $options['icon_color'] === 'custom' ) ? $options['icon_custom_color'] : $options['icon_color'];
				$style      .= ' color:' . $icon_color . '; ';
			}

			if ( ! empty( $options['icon_size'] ) ) {
				$icon_size = $options['icon_size'];
				$style     .= ' size:' . $icon_size . '; ';
			}
		}

		$options['attributes']['style'] = $style;

		if ( ! isset( $options['attributes']['id'] ) ) {
			$options['attributes']['id'] = $options['icon_el_class'];
		}

		if ( ! isset( $options['attributes']['class'] ) ) {
			$options['attributes']['class'] = $options['icon_el_class'];
		}

		$options['attributes']['class'] .= ' ' . $options['icon'];

		$attr_html = VSP_Helper::array_to_html_attr( array_filter( $options['attributes'] ) );


		return '<i ' . $attr_html . '> </i>';
	}
}

if ( ! function_exists( 'vsp_vc_remap_group' ) ) {
	/**
	 * @param array  $args
	 * @param array  $remap_ids
	 * @param string $group
	 *
	 * @return array|mixed
	 */
	function vsp_vc_remap_group( $args = array(), $remap_ids = array(), $group = '' ) {
		$is_params = false;
		$loop      = $args;
		if ( isset( $args['params'] ) ) {
			$is_params = true;
			$loop      = $args['params'];
		}

		foreach ( $loop as $i => $el ) {
			if ( isset( $el['param_name'] ) && ( isset( $defaults[ $el['param_name'] ] ) || in_array( $el['param_name'], $remap_ids ) ) ) {
				$loop[ $i ]['group'] = $group;
			}
		}

		if ( $is_params ) {
			$args['params'] = $loop;
		} else {
			$args = $loop;
		}

		return $args;
	}
}
