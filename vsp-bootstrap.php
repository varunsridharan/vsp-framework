<?php
/**
 *------------------------------------------------------------------------------------------------
 *
 * VSP Framework
 * A Lightweight and easy-to-use WordPress Plugin/Theme Framework
 *
 * ------------------------------------------------------------------------------------------------
 *
 * Copyright 2018 WordPress-Settings-Framework <varunsridharan23@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * ------------------------------------------------------------------------------------------------
 *
 * Framework Name: VSP Framework
 * Version: 010420180654 - Build 1
 * Author: Varun Sridharan
 * Author URI:http://varunsridharan.in
 * Text Domain:vsp-framework
 * Domain Path: languages/
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vsp_version' ) ) {
	/**
	 * Provides The Version Number For VSP
	 *
	 * @return string
	 */
	function vsp_version() {
		return '190420180523-Build-1';
	}

	$upload_dir = wp_upload_dir( null, false );
	defined( 'VSP_VERSION' ) || define( 'VSP_VERSION', vsp_version() );
	defined( 'VSP_PATH' ) || define( 'VSP_PATH', plugin_dir_path( __FILE__ ) );
	defined( 'VSP_URL' ) || define( 'VSP_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
	defined( 'VSP_CORE' ) || define( 'VSP_CORE', VSP_PATH . 'core/' );
	defined( 'VSP_LOG_DIR' ) || define( 'VSP_LOG_DIR', $upload_dir['basedir'] . '/vsp-logs/' );


	require_once VSP_CORE . 'class-autoloader.php';
	require_once VSP_CORE . 'class-cache.php';
	require_once VSP_PATH . 'vsp-functions.php';

	require_once VSP_PATH . 'functions/options.php';
	require_once VSP_PATH . 'functions/wp-replacement.php';
	require_once VSP_PATH . 'functions/general-functions.php';
	require_once VSP_PATH . 'functions/admin-notices-functions.php';
	require_once VSP_CORE . 'class-base-setup.php';
	require_once VSP_PATH . 'vsp-hooks.php';
	require_once VSP_PATH . 'core/modules/system-tools/class-system-status-report.php';

	do_action( 'vsp_framework_load_lib_integrations' );

	do_action( 'vsp_framework_loaded' );

	if ( vsp_is_ajax() ) {
		require_once VSP_CORE . 'class-core-ajax.php';
	}

	/**
	 * Framework load text domain
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	if ( file_exists( VSP_PATH . '/languages/' . get_locale() . '.mo' ) ) {
		load_textdomain( 'vsp-framework', VSP_PATH . '/languages/' . get_locale() . '.mo' );
	}

	do_action( 'vsp_framework_init' );
}
