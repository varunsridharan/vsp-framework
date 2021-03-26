<?php
/**
 * VSP Framework | A Lightweight and easy-to-use WordPress Plugin/Theme Framework
 *
 * Copyright 2018 WordPress-Settings-Framework <varunsridharan23@gmail.com>
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * Framework Name: VSP Framework
 * Plugin Name: VSP Framework
 * Version: 0.8.9.8
 * Author: Varun Sridharan
 * Author URI:http://varunsridharan.in
 * Text Domain:vsp-framework
 * Domain Path: languages/
 */

use Varunsridharan\PHP\Autoloader;
use VSP\Deprecation\Actions;
use VSP\Deprecation\Filters;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\VSP\Framework', false ) ) {
	try {
		$upload_dir = wp_upload_dir( null, false );
		defined( 'VSP_VERSION' ) || define( 'VSP_VERSION', '0.8.9.8' );
		defined( 'VSP_PATH' ) || define( 'VSP_PATH', plugin_dir_path( __FILE__ ) );
		defined( 'VSP_URL' ) || define( 'VSP_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
		defined( 'VSP_CORE' ) || define( 'VSP_CORE', VSP_PATH . 'core/' );
		defined( 'VSP_LOG_DIR' ) || define( 'VSP_LOG_DIR', $upload_dir['basedir'] . '/vsp-logs/' );

		if ( file_exists( VSP_PATH . 'vendor/autoload.php' ) ) {
			require_once VSP_PATH . 'vendor/autoload.php';
		}

		if ( ! class_exists( '\Varunsridharan\PHP\Autoloader' ) ) {
			throw new ErrorException( esc_html__( 'Framework Autoloader Not Found', 'vsp-framework' ) );
		}

		new Autoloader( 'VSP\\', VSP_PATH . 'includes/', array(
			'prepend'  => true,
			'classmap' => __DIR__ . '/vsp-classmaps.php',
		) );

		Actions::instance();
		Filters::instance();

		require_once __DIR__ . '/vsp-functions.php';
		require_once __DIR__ . '/vsp-hooks.php';

		do_action( 'vsp/loaded' );

		if ( vsp_is_ajax() ) {
			require_once __DIR__ . '/includes/class-ajax.php';
		}

		do_action( 'vsp/init' );
	} catch ( Exception $exception ) {
		$path = str_replace( untrailingslashit( ABSPATH ), '', plugin_dir_path( __DIR__ ) );
		$msg  = '<h4 style="text-align: center">' . esc_html__( 'Autoloder For VSP-Framework Not Found.', 'vsp-framework' ) . '</h4>';
		$msg  .= '<i style="text-align: center; display: block;">' . esc_html__( 'Please Contact The Author Of The Plugin', 'vsp-framework' ) . '</i><br/>';
		$msg  .= '<strong>' . esc_html__( 'Plugin Path :', 'vsp-framework' ) . '</strong>' . ' <code style="padding: 2px 5px;background: #ffe6ee;border: 1px solid #ffb3cb;border-radius: 5px;margin-left: 10px;">' . $path . '</code> <br/> <br/>';
		$msg  .= '<strong style="font-style: italic; color:red;">' . esc_html__( 'Error : ', 'vsp-framework' ) . ' </strong> ' . $exception->getMessage();
		wp_die( $msg );
	}
}
