<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'vsp_register_assets', 1 );

if ( ! function_exists( 'vsp_register_assets' ) ) {
	/**
	 * Registers Basic Framework Styles / Scripts to WP
	 */
	function vsp_register_assets() {
		wp_register_script( 'vsp-framework', vsp_js( 'vsp-framework.js', true ), [ 'wponion-core' ], '1.0', true );
		wp_register_style( 'vsp-framework', vsp_css( 'vsp-framework.css', true ), [], '1.0' );
	}
}

if ( ! function_exists( 'vsp_load_core_assets' ) ) {
	/**
	 * Custom Function To Load All Core Assets.
	 */
	function vsp_load_core_assets() {
		wp_enqueue_style( 'vsp-framework' );
		wp_enqueue_script( 'vsp-framework' );
	}
}
