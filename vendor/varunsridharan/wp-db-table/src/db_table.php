<?php


namespace Varunsridharan\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

use TheLeague\Database\Query_Builder;

if ( ! class_exists( '\Varunsridharan\WordPress\DB_Table' ) ) {
	/**
	 * Class DB_Table
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
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
	abstract class DB_Table extends Query_Builder {

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
		 * @var \wpdb
		 */
		protected $db = false;

		/**
		 * Stores Multiple Class Instance.
		 *
		 * @var array
		 */
		protected static $_instances = array();

		/**
		 * DB_Table constructor.
		 */
		public function __construct() {
			parent::__construct( false );
			$table_name = $this->table_name();
			if ( ! empty( $table_name ) ) {
				$this->name = $table_name;
			}

			$db_version = $this->table_version();
			if ( ! empty( $db_version ) ) {
				$this->db_version = $db_version;
			}

			$this->setup();
			if ( empty( $this->name ) || empty( $this->db_version_key ) ) {
				return;
			}
			$this->get_db_version();
			$this->set_wpdb_tables();

			$set_schema = $this->set_schema();
			if ( ! empty( $set_schema ) ) {
				$this->schema = $set_schema;
			}

			$this->add_hooks();
		}

		/**
		 * Returns Current Instance / create a new instance
		 *
		 * @return self|static
		 */
		public static function instance() {
			if ( ! isset( self::$_instances[ static::class ] ) ) {
				self::$_instances[ static::class ] = new static();
			}
			return self::$_instances[ static::class ];
		}

		/**
		 * Setup this database table
		 */
		protected abstract function set_schema();

		/**
		 * Upgrade this database table
		 */
		protected abstract function upgrade();

		/**
		 * Provides Table Name.
		 *
		 * @return mixed
		 */
		protected abstract function table_name();

		/**
		 * Provides Table Version Number.
		 *
		 * @return mixed
		 */
		protected abstract function table_version();

		/**
		 * Update table version & references.
		 *
		 * Hooked to the "switch_blog" action.
		 *
		 * @param int $site_id The site being switched to
		 */
		public function switch_blog( $site_id = 0 ) {
			if ( ! $this->is_global() ) {
				$this->db_version = \get_blog_option( $site_id, $this->db_version_key, false );
			}
			$this->set_wpdb_tables();
		}

		/**
		 * Maybe upgrade the database table. Handles creation & schema changes.
		 *
		 * Hooked to the "admin_init" action.
		 */
		public function maybe_upgrade() {
			if ( ! $this->exists() ) {
				$this->create();
			} else {
				$needs_upgrade = version_compare( $this->version, $this->db_version, '>=' );

				if ( true === $needs_upgrade ) {
					return;
				}

				if ( $this->is_global() && ! \wp_should_upgrade_global_tables() ) {
					return;
				}

				$this->exists() ? $this->upgrade() : $this->create();
			}

			if ( $this->exists() ) {
				$this->set_db_version();
			}
		}

		/**
		 * Setup the necessary table variables
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

			$this->table = $this->db->{$this->name};

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
		 */
		private function set_db_version() {
			$this->version = $this->db_version;
			$this->is_global() ? \update_network_option( null, $this->db_version_key, $this->version ) : \update_option( $this->db_version_key, $this->version );
		}

		/**
		 * Get the table version from the database
		 *
		 * Global table version from "_sitemeta" on the main network
		 */
		private function get_db_version() {
			$this->version = $this->is_global() ? \get_network_option( null, $this->db_version_key, false ) : \get_option( $this->db_version_key, false );
		}

		/**
		 * Add class hooks to WordPress actions
		 */
		private function add_hooks() {
			\add_action( 'switch_blog', array( $this, 'switch_blog' ) );
		}

		/**
		 * Create the table
		 */
		private function create() {
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			if ( ! function_exists( 'dbDelta' ) ) {
				return false;
			}

			$query   = "CREATE TABLE {$this->table} ( {$this->schema} ) {$this->charset_collation};";
			$created = dbDelta( array( $query ) );
			if ( ! empty( $created ) ) {
				$this->after_table_created();
			}
			return ! empty( $created );
		}

		/**
		 * Works As A Built In Hook To Provide a Option to run after table is created.
		 */
		protected function after_table_created() {
		}

		/**
		 * Check if table already exists
		 *
		 * @return bool
		 */
		private function exists() {
			$query       = 'SHOW TABLES LIKE %s';
			$like        = $this->db->esc_like( $this->table );
			$prepared    = $this->db->prepare( $query, $like );
			$table_exist = $this->db->get_var( $prepared );
			return ! empty( $table_exist );
		}

		/**
		 * Check if table is global
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
		 * @param string $name The name of the database table
		 *
		 * @return string Sanitized database table name
		 */
		private function sanitize_table_name( $name = '' ) {
			$accents = \remove_accents( $name );
			$lower   = \sanitize_key( $accents );
			$under   = str_replace( '-', '_', $lower );
			$single  = str_replace( '__', '_', $under );
			$clean   = trim( $single, '_' );
			return empty( $clean ) ? false : $clean;
		}
	}
}
