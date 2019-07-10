<?php

namespace Varunsridharan\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\Varunsridharan\WordPress\Plugin_Version_Management' ) ) {
	/**
	 * Class Plugin_Version_Management
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Plugin_Version_Management {
		/**
		 * Default Database Key.
		 *
		 * @var string
		 */
		private $db_key = '_vs_wp_plugin_upgrader';

		/**
		 * Data From DB.
		 *
		 * @var array
		 */
		protected $db = false;

		/**
		 * Stores Plugin Slug.
		 *
		 * @var bool
		 */
		protected $slug = false;

		/**
		 * Stores New Plugin Version.
		 *
		 * @var bool
		 */
		protected $new_v = false;

		/**
		 * Stores Version & Its Callback
		 *
		 * @example  array('1.0' => 'plugin_v1_upgrade')
		 * @var array
		 */
		protected $versions = array();

		/**
		 * @var array
		 * @access
		 */
		protected $callbacks = array();

		/**
		 * Custom Option Name To Save Information in DB.
		 *
		 * @var string
		 */
		protected $option_name = '';

		/**
		 * Set True To Save Upgrade Log.
		 *
		 * @var bool
		 */
		protected $logs = false;

		/**
		 * Plugin_Version_Management constructor.
		 *
		 * @param array $config
		 * @param array $versions
		 */
		public function __construct( $config = array(), $versions = array() ) {
			$args              = wp_parse_args( $config, array(
				'slug'        => false,
				'version'     => false,
				'logs'        => true,
				'option_name' => true,
			) );
			$this->slug        = $args['slug'];
			$this->new_v       = $args['version'];
			$this->option_name = ( empty( $args['option_name'] ) ) ? true : $args['option_name'];
			$this->logs        = $args['logs'];
			$this->versions    = array_keys( $versions );
			$this->callbacks   = $versions;
			sort( $this->versions );
			$this->get_db_values();
		}

		/**
		 * Returns Current Plugin's Version.
		 *
		 * @return bool|mixed
		 */
		public function version() {
			return ( isset( $this->db['version'] ) ) ? $this->db['version'] : false;
		}

		/**
		 * Returns Plugin's Activation Logs.
		 *
		 * @return bool|mixed
		 */
		public function logs() {
			return ( isset( $this->db['logs'] ) ) ? $this->db['logs'] : false;
		}

		/**
		 * Retrives Saved Values From DB.
		 *
		 * @return $this
		 */
		protected function get_db_values() {
			if ( empty( $this->db ) ) {
				$option   = ( true === $this->option_name ) ? $this->db_key : $this->option_name;
				$this->db = get_option( $option, false );
				$this->db = ( ! is_array( $this->db ) ) ? array() : $this->db;

				if ( true === $this->option_name ) {
					$this->db = ( isset( $this->db[ $this->slug ] ) ) ? $this->db[ $this->slug ] : array();
				}

				$this->db = wp_parse_args( $this->db, array(
					'version' => false,
					'logs'    => false,
				) );
			}
			return $this->db;
		}

		/**
		 * Triggers Version Upgrade Callback.
		 *
		 * @return $this
		 */
		public function run() {
			if ( ! empty( $this->slug ) && ! empty( $this->new_v ) && $this->new_v !== $this->version() ) {
				$done = false;
				if ( ! empty( $this->version() ) ) {
					foreach ( $this->versions as $ver ) {
						if ( $this->version() === $ver ) {
							continue;
						}

						if ( version_compare( $ver, $this->version(), '>=' ) && version_compare( $ver, $this->new_v, '<=' ) ) {
							$done = call_user_func_array( $this->callbacks[ $ver ], array( $this->version(), $ver ) );
							if ( ! $done ) {
								break;
							}
						}
					}
				} elseif ( empty( $this->version() ) && isset( $this->callbacks[ $this->new_v ] ) && is_callable( $this->callbacks[ $this->new_v ] ) ) {
					$done = call_user_func_array( $this->callbacks[ $this->new_v ], array( '', $this->new_v ) );
				}
				if ( true === $done || ! isset( $this->callbacks[ $this->new_v ] ) ) {
					$this->update_log();
				}
			}
			return $this;
		}

		/**
		 * Updates Log Informations.
		 */
		protected function update_log() {
			$logs = ( $this->logs ) ? $this->logs() : false;
			$data = array( 'version' => $this->new_v );
			if ( true === $this->logs ) {
				$logs                 = ( ! is_array( $logs ) ) ? array() : $logs;
				$logs[ $this->new_v ] = array(
					'user_id' => get_current_user_id(),
					'time'    => current_time( 'timestamp' ),
					'from'    => $this->version(),
				);
			}
			$data['logs'] = $logs;
			return $this->save_db_values( $data );
		}

		/**
		 * Updated Database With new Values.
		 *
		 * @param $data
		 *
		 * @return $this
		 */
		protected function save_db_values( $data ) {
			$option_name = ( true === $this->option_name ) ? $this->db_key : $this->option_name;
			$stored_data = get_option( $option_name, false );
			$stored_data = ( ! is_array( $stored_data ) ) ? array() : $stored_data;

			if ( true === $this->option_name ) {
				$stored_data[ $this->slug ] = $data;
			} else {
				$stored_data = wp_parse_args( $data, $stored_data );
			}
			update_option( $option_name, $stored_data, false );
			return $this;

		}
	}
}
