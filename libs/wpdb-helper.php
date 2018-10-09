<?php
/**
 * @name: WPDB Helper
 * @version: 1.1.0
 *
 * @github: https://github.com/meshakeeb/wp-query-builder
 */

if ( ! class_exists( 'TheLeague\Database\Query_Builder' ) ) {
	vsp_load_file( __DIR__ . '/wp-query-builder/traits/*.php' );
	require_once __DIR__ . '/wp-query-builder/class-query-builder.php';
}
