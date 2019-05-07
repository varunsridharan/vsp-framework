<?php
/**
 * WordPress Query Builder
 *
 * @package      TheLeague\Database
 * @copyright    Copyright (C) 2018, The WordPress League - info@thewpleague.com
 * @link         http://thewpleague.com
 * @since        1.0.9
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Query Builder
 * Version:           1.0.9
 * Plugin URI:        http://thewpleague.com/wp-query-builder/
 * Description:       An expressive query builder for WordPress. Build for developers by developers.
 * Author:            The WordPress League
 * Author URI:        http://thewpleague.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 4.0
 * Tested up to:      4.8
 */

defined( 'ABSPATH' ) || exit;

/**
 * PSR-4 Autoload.
 */
include dirname( __FILE__ ) . '/vendor/autoload.php';

use TheLeague\Database\Database;

if ( ! function_exists( 'wp_query_builder' ) ) {
	/**
	 * Make wp query builder as global scope.
	 *
	 * @param  string $table_name A Database instance id.
	 * @return Database Database object instance.
	 */
	function wp_query_builder( $table_name ) {
		return Database::table( $table_name );
	}
}
