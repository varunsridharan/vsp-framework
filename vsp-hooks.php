<?php
/**
 * VSP Framework Basic Setup / Hooks To WP
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_action( 'vsp_framework_loaded', 'vsp_init_admin_notices', 1 );
add_action( 'vsp_framework_loaded', 'vsp_on_framework_loaded' );

if ( ! function_exists( 'vsp_on_framework_loaded' ) ) {
	/**
	 * Creates A Instances For WP Importers
	 */
	function vsp_on_framework_loaded() {
		VSP_WP_Importers::instance();
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
		$js = [
			'fancybox'  => vsp_url( 'assets/vendors/fancybox/jquery.fancybox.min.js', true ),
			'addons'    => vsp_js( 'vsp-addons.js', true ),
			'plugins'   => vsp_js( 'vsp-plugins.js', true ),
			'framework' => vsp_js( 'vsp-framework.js', true ),
			'vuejs'     => 'https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.min.js',
		];

		$css = [
			'framework' => vsp_css( 'vsp-framework.css', true ),
			'fancybox'  => vsp_url( 'assets/vendors/fancybox/jquery.fancybox.min.css', true ),
			'addons'    => vsp_css( 'vsp-addons.css', true ),
		];

		vsp_register_script( 'vsp-plugins', $js['plugins'], [ 'jquery' ], '1.0', true );
		vsp_register_script( 'vuejs', $js['vuejs'], [], '1.3.8', true );
		vsp_register_script( 'vsp-addons', $js['addons'], [ 'vuejs', 'jquery' ], '1.0', false );
		vsp_register_script( 'vsp-framework', $js['framework'], [ 'jquery', 'vsp-plugins' ], '1.0', true );
		vsp_register_script( 'vsp-fancybox', $js['fancybox'], [ 'jquery' ], '1.0.16', true );

		vsp_register_style( 'vsp-framework', $css['framework'], [], '1.0' );
		vsp_register_style( 'vsp-addons', $css['addons'], [], '1.0' );
		vsp_register_style( 'vsp-fancybox', $css['fancybox'], [], '1.0' );

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


if ( ! function_exists( 'vsp_init_admin_notices' ) ) {
	/**
	 * Creats A Instance Of Admin Notice Class
	 *
	 * @use vsp_framework_init
	 */
	function vsp_init_admin_notices() {
		if ( vsp_is_admin() || vsp_is_ajax() ) {
			vsp_notices();
		}
	}
}
