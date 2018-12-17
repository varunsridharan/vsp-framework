<?php
/**
 * A Base WordPress Database Table class
 *
 * Project: vsp-framework
 * File: class-db.php
 * Date: 11-07-2018
 * Time: 03:58 PM
 *
 * @link    http://github.com/varunsridharan/vsp-framework/
 * @downloadLink https://github.com/stuttter/wp-db-table/blob/master/class-wp-db-table.php
 * @version 1.0
 * @since   1.0
 *
 * @package   vsp-framework/core/abstract
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

namespace VSP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'DB_Table' ) ) {
	vsp_load_file( VSP_PATH . 'vendor/thewpleague/wp-query-builder/src/traits/*.php' );
	require_once VSP_PATH . 'vendor/thewpleague/wp-query-builder/src/class-query-builder.php';
	vsp_load_lib( 'wp-db-table' );

	/**
	 * Class VSP_DB_Table
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 *
	 * A base WordPress database table class, which facilitates the creation of
	 * and schema changes to individual database tables.
	 *
	 * This class is intended to be extended for each unique database table,
	 * including global multisite tables and users tables.
	 *
	 * It exists to make managing database tables in WordPress as easy as possible.
	 *
	 * Extending this class comes with several automatic benefits:
	 * - Activation hook makes it great for plugins
	 * - Tables store their versions in the database independently
	 * - Tables upgrade via independent upgrade abstract methods
	 * - Multisite friendly - site tables switch on "switch_blog" action
	 */
	abstract class DB_Table extends \Varunsridharan\WordPress\DB_Table {

	}
}
