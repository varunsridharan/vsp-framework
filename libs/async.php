<?php
/**
 * Name: WP Async
 * Version: 1.0
 */
define( "VSP_ASYNC_PATH", plugin_dir_path( __FILE__ ) );

if ( ! class_exists( "WP_Async_Request", false ) && vsp_wc_active() === true ) {
	if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/includes/libraries/wp-async-request.php' ) ) {
		include( WP_PLUGIN_DIR . '/woocommerce/includes/libraries/wp-async-request.php' );
	}
} else {
	if ( file_exists( VSP_ASYNC_PATH . 'wp-async/wp-async-request.php' ) ) {
		include( VSP_ASYNC_PATH . 'wp-async/wp-async-request.php' );
	}
}


if ( ! class_exists( "WP_Background_Process", false ) && vsp_wc_active() === true ) {
	if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/includes/libraries/wp-background-process.php' ) ) {
		include( WP_PLUGIN_DIR . '/woocommerce/includes/libraries/wp-background-process.php' );
	}
} else {
	if ( file_exists( VSP_ASYNC_PATH . 'wp-async/wp-background-process.php' ) ) {
		include( VSP_ASYNC_PATH . 'wp-async/wp-background-process.php' );
	}
}