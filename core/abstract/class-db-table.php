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

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'VSP_DB_Table' ) ) {
	vsp_load_lib( 'wpdb' );

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
	abstract class VSP_DB_Table extends WPDB_Helper {
		/**
		 * Table name, without the global table prefix
		 *
		 * @var string
		 */
		protected $name = '';

		/**
		 *  Database version
		 *
		 * @var int
		 */
		protected $version = 0;

		/**
		 * Is this table for a site, or global
		 *
		 * @var bool
		 */
		protected $global = false;

		/**
		 * Database version key (saved in _options or _sitemeta)
		 *
		 * @var string
		 */
		protected $db_version_key = '';

		/**
		 * Current database version
		 *
		 * @var int
		 */
		protected $db_version = 0;

		/**
		 * Table name
		 *
		 * @var string
		 */
		protected $table_name = '';

		/**
		 * Table schema
		 *
		 * @var string
		 */
		protected $schema = '';

		/**
		 * @var string
		 */
		protected $prefix = null;

		/**
		 * Database character-set & collation for table
		 *
		 * @var string
		 */
		protected $charset_collation = '';

		/**
		 * WPDB Database object (usually $GLOBALS['wpdb'])
		 *
		 * @var bool
		 */
		protected $db = false;

		/**
		 * Hook into queries, admin screens, and more!
		 * VSP_DB_Table constructor.
		 */
		public function __construct() {
			$this->setup();
			if ( empty( $this->name ) || empty( $this->db_version_key ) ) {
				return;
			}
			$this->get_db_version();
			$this->set_wpdb_tables();
			$this->set_schema();
			$this->add_hooks();
		}

		/**
		 * Setup this database table
		 *
		 * @since 1.1.0
		 */
		protected abstract function set_schema();

		/**
		 * Upgrade this database table
		 *
		 * @since 1.1.0
		 */
		protected abstract function upgrade();

		/**
		 * Update table version & references.
		 *
		 * Hooked to the "switch_blog" action.
		 *
		 * @since 1.1.0
		 *
		 * @param int $site_id The site being switched to
		 */
		public function switch_blog( $site_id = 0 ) {
			if ( ! $this->is_global() ) {
				$this->db_version = get_blog_option( $site_id, $this->db_version_key, false );
			}

			$this->set_wpdb_tables();
		}

		/**
		 * Maybe upgrade the database table. Handles creation & schema changes.
		 *
		 * Hooked to the "admin_init" action.
		 *
		 * @since 1.1.0
		 */
		public function maybe_upgrade() {
			if ( ! $this->exists() ) {
				$this->create();
			}

			$needs_upgrade = version_compare( (int) $this->db_version, (int) $this->version, '>=' );

			if ( true === $needs_upgrade ) {
				return;
			}

			if ( $this->is_global() && ! wp_should_upgrade_global_tables() ) {
				return;
			}

			$this->exists() ? $this->upgrade() : $this->create();

			if ( $this->exists() ) {
				$this->set_db_version();
			}
		}

		/**
		 * Setup the necessary table variables
		 *
		 * @since 1.1.0
		 */
		private function setup() {
			$this->db = isset( $GLOBALS['wpdb'] ) ? $GLOBALS['wpdb'] : false;

			if ( false === $this->db ) {
				return;
			}

			$this->prefix = $this->db->prefix;
			$this->name   = $this->sanitize_table_name( $this->name );

			if ( false === $this->name ) {
				return;
			}

			if ( empty( $this->db_version_key ) ) {
				$this->db_version_key = "wpdb_{$this->name}_version";
			}
		}

		/**
		 * Modify the database object and add the table to it
		 *
		 * This must be done directly because WordPress does not have a mechanism
		 * for manipulating them safely
		 *
		 * @since 1.1.0
		 */
		private function set_wpdb_tables() {
			if ( $this->is_global() ) {
				$prefix                       = $this->db->get_blog_prefix( 0 );
				$this->db->{$this->name}      = "{$prefix}{$this->name}";
				$this->db->ms_global_tables[] = $this->name;
			} else {
				$prefix                  = $this->db->get_blog_prefix( null );
				$this->db->{$this->name} = "{$prefix}{$this->name}";
				$this->db->tables[]      = $this->name;
			}

			$this->table_name = $this->db->{$this->name};

			if ( ! empty( $this->db->charset ) ) {
				$this->charset_collation = "DEFAULT CHARACTER SET {$this->db->charset}";
			}

			if ( ! empty( $this->db->collate ) ) {
				$this->charset_collation .= " COLLATE {$this->db->collate}";
			}
		}

		/**
		 * Set the database version for the table
		 *
		 * Global table version in "_sitemeta" on the main network
		 *
		 * @since 1.1.0
		 */
		private function set_db_version() {
			$this->db_version = $this->version;
			$this->is_global() ? update_network_option( null, $this->db_version_key, $this->version ) : update_option( $this->db_version_key, $this->version );
		}

		/**
		 * Get the table version from the database
		 *
		 * Global table version from "_sitemeta" on the main network
		 *
		 * @since 1.1.0
		 */
		private function get_db_version() {
			$this->db_version = $this->is_global() ? get_network_option( null, $this->db_version_key, false ) : get_option( $this->db_version_key, false );
		}

		/**
		 * Add class hooks to WordPress actions
		 *
		 * @since 1.1.0
		 */
		private function add_hooks() {
			add_action( 'switch_blog', array( $this, 'switch_blog' ) );
		}

		/**
		 * Create the table
		 *
		 * @since 1.1.0
		 */
		private function create() {
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			if ( ! function_exists( 'dbDelta' ) ) {
				return false;
			}

			$query   = "CREATE TABLE {$this->table_name} ( {$this->schema} ) {$this->charset_collation};";
			$created = dbDelta( array( $query ) );
			return ! empty( $created );
		}

		/**
		 * Check if table already exists
		 *
		 * @since 1.1.0
		 *
		 * @return bool
		 */
		private function exists() {
			$query       = 'SHOW TABLES LIKE %s';
			$like        = $this->db->esc_like( $this->table_name );
			$prepared    = $this->db->prepare( $query, $like );
			$table_exist = $this->db->get_var( $prepared );
			return ! empty( $table_exist );
		}

		/**
		 * Check if table is global
		 *
		 * @since 1.2.0
		 *
		 * @return bool
		 */
		private function is_global() {
			return ( true === $this->global );
		}

		/**
		 * Sanitize a table name string
		 *
		 * Applies the following formatting to a string:
		 * - No accents
		 * - No special characters
		 * - No hyphens
		 * - No double underscores
		 * - No trailing underscores
		 *
		 * @since 1.3.0
		 *
		 * @param string $name The name of the database table
		 *
		 * @return string Sanitized database table name
		 */
		private function sanitize_table_name( $name = '' ) {
			$accents = remove_accents( $name );
			$lower   = sanitize_key( $accents );
			$under   = str_replace( '-', '_', $lower );
			$single  = str_replace( '__', '_', $under );
			$clean   = trim( $single, '_' );
			return empty( $clean ) ? false : $clean;
		}
	}
}
