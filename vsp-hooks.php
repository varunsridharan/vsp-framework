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
			'blockui'     => vsp_debug_file( 'vendors/blockui/jquery.blockui.js', 'assets' ),
			'simscroll'   => vsp_debug_file( 'vendors/simscroll/simscroll.js', 'assets' ),
			'vspajax'     => vsp_debug_file( 'vendors/vspajax/jquery.vsp-ajax.js', 'assets' ),
			'fancybox'    => vsp_debug_file( 'vendors/fancybox/jquery.fancybox.js', 'assets' ),
			'addons'      => vsp_debug_file( 'vsp-addons.js', 'js' ),
			'plugins'     => vsp_debug_file( 'vsp-plugins.js', 'js' ),
			'framework'   => vsp_debug_file( 'vsp-framework.js', 'js' ),
			'vuejs'       => vsp_js( 'vue.min.js' ),
			'lodash'      => vsp_js( 'lodash.min.js' ),
			'sweatalert'  => vsp_js( 'sweatAlert.min.js' ),
			'wp-js-hooks' => vsp_debug_file( 'assets/vendors/wp-js-hooks/wp-js-hooks.min.js', 'url' ),
			'utils'       => vsp_debug_file( 'assets/js/vsp-utils.js', 'url' ),
		];

		$css = [
			'framework' => vsp_debug_file( 'vsp-framework.css', 'css' ),
			'plugins'   => vsp_debug_file( 'vsp-plugins.css', 'css' ),
			'fancybox'  => vsp_debug_file( 'vendors/fancybox/jquery.fancybox.css', 'assets' ),
			'addons'    => vsp_debug_file( 'vsp-addons.css', 'css' ),
		];

		vsp_register_script( 'wp-js-hooks', $js['wp-js-hooks'], [], '1.0', false );
		vsp_register_script( 'vsp-simscroll', $js['simscroll'], [ 'jquery' ], '1.3.8', true );
		vsp_register_script( 'vsp-blockui', $js['blockui'], [ 'jquery' ], '1.0.16', true );
		vsp_register_script( 'vsp-ajax', $js['vspajax'], [ 'jquery' ], '1.0', true );
		vsp_register_script( 'vsp-plugins', $js['plugins'], [ 'jquery' ], '1.0', true );
		vsp_register_script( 'vuejs', $js['vuejs'], [], '1.3.8', true );
		vsp_register_script( 'lodash', $js['lodash'], [], '1.3.8', true );
		vsp_register_script( 'sweatalert', $js['sweatalert'], [], '2.1.0', true );
		vsp_register_script( 'vsp-addons', $js['addons'], [ 'vuejs', 'lodash', 'sweatalert' ], '1.0', false );
		vsp_register_script( 'vsp-framework', $js['framework'], [ 'jquery' ], '1.0', true );
		vsp_register_style( 'vsp-plugins', $css['plugins'] );
		vsp_register_style( 'vsp-framework', $css['framework'], [], '1.0' );
		vsp_register_style( 'vsp-addons', $css['addons'], [], '1.0' );
		vsp_register_style( 'vsp-fancybox', $css['fancybox'], [], '1.0' );
		vsp_register_script( 'vsp-fancybox', $js['fancybox'], [ 'jquery' ], '1.0.16', true );
		vsp_register_script( 'vsp-utils', $js['utils'], [ 'jquery', 'wp-js-hooks', 'vsp-blockui' ], '1.0', true );
	}
}


if ( ! function_exists( "vsp_init_admin_notices" ) ) {
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