<?php
/**
 * VSP Framework Basic Setup / Hooks To WP
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

use VSP\Modules\WordPress\Importers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'vsp_framework_loaded', 'vsp_on_framework_loaded' );

if ( ! function_exists( 'vsp_on_framework_loaded' ) ) {
	/**
	 * Creates A Instances For WP Importers
	 */
	function vsp_on_framework_loaded() {
		Importers::instance();
	}
}


if ( vsp_is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'vsp_register_assets', 1 );
}

if ( ! function_exists( 'vsp_register_assets' ) ) {
	/**
	 * Registers Basic Framework Styles / Scripts to WP
	 *
	 * @uses admin_enqueue_scripts
	 */
	function vsp_register_assets() {
		$js  = [ 'framework' => vsp_js( 'vsp-framework.js', true ) ];
		$css = [ 'framework' => vsp_css( 'vsp-framework.css', true ) ];

		vsp_register_script( 'vsp-framework', $js['framework'], [ 'jquery', 'wponion-core' ], '1.0', true );
		vsp_register_style( 'vsp-framework', $css['framework'], [], '1.0' );
	}
}

if ( ! function_exists( 'vsp_load_core_assets' ) ) {
	/**
	 * Custom Function To Load All Core Assets.
	 */
	function vsp_load_core_assets() {
		if ( wp_style_is( 'vsp-framework', 'registered' ) && false === wp_style_is( 'vsp-framework' ) ) {
			wp_enqueue_style( 'vsp-framework' );
		}

		if ( wp_script_is( 'vsp-framework', 'registered' ) && false === wp_script_is( 'vsp-framework' ) ) {
			wp_enqueue_script( 'vsp-framework' );
		}
	}
}
