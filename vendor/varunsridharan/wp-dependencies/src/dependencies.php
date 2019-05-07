<?php
/**
 * WP Dependencies Checker.
 * Provides Function To Check if a plugin is active/inactive & function to compare versions.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.4
 * @since 1.0
 * @link https://github.com/varunsridharan/wp-dependencies
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

namespace Varunsridharan\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\Varunsridharan\WordPress\Dependencies' ) ) {
	require_once __DIR__ . '/functions.php';

	/**
	 * Class Dependencies
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Dependencies {
		/**
		 * Loads Plugin.php if its not auto included.
		 *
		 * @static
		 */
		protected static function load_file() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
		}

		/**
		 * Checks if given plugin is installed.
		 *
		 * @param $file
		 *
		 * @static
		 * @return bool
		 */
		public static function is_installed( $file ) {
			self::load_file();
			$plugins = get_plugins();
			return ( isset( $plugins[ $file ] ) ) ? $plugins[ $file ] : false;
		}

		/**
		 * Checks If Plugin is Active.
		 *
		 * @param $file
		 *
		 * @static
		 * @return bool
		 */
		public static function is_active( $file ) {
			self::load_file();
			return ( is_plugin_active( $file ) || is_plugin_active_for_network( $file ) );
		}

		/**
		 * Checks If Plugin is Active In Network Wide.
		 *
		 * @param $file
		 *
		 * @static
		 * @return bool
		 */
		public static function is_network_active( $file ) {
			self::load_file();
			return is_plugin_active_for_network( $file );
		}

		/**
		 * Checks If Plugin is Active Site Wide.
		 *
		 * @param $file
		 *
		 * @static
		 * @return bool
		 */
		public static function is_site_active( $file ) {
			self::load_file();
			return is_plugin_active( $file );
		}

		/**
		 * Checks If Plugin Is InActive.
		 *
		 * @param $file
		 *
		 * @static
		 * @return bool
		 */
		public static function is_inactive( $file ) {
			self::load_file();
			return is_plugin_inactive( $file );
		}

		/**
		 * Reads Plugin's Base File And Gathers Information.
		 *
		 * @param      $file
		 * @param bool $markup
		 * @param bool $translate
		 *
		 * @static
		 * @return array
		 */
		public static function plugin_data( $file, $markup = true, $translate = true ) {
			self::load_file();
			$plugins = get_plugins();
			if ( ! empty( $plugins ) && isset( $plugins[ $file ] ) ) {
				return $plugins[ $file ];
			}
			return get_plugin_data( WP_PLUGIN_DIR . '/' . $file, $markup, $translate );
		}

		/**
		 * Returns Plugin's Version.
		 *
		 * @param $plugin_file
		 *
		 * @static
		 * @return string|bool
		 */
		public static function version( $plugin_file ) {
			$return = false;
			switch ( strtolower( $plugin_file ) ) {
				case 'wordpress':
					global $wp_version;
					$return = $wp_version;
					break;
				case 'php':
					$return = PHP_VERSION;
					break;
				case 'mysql':
					global $wpdb;
					$return = $wpdb->db_version();
					break;
				default:
					$data = self::plugin_data( $plugin_file, true, false );
					if ( isset( $data['Version'] ) ) {
						$return = $data['Version'];
					}
					break;
			}
			return $return;
		}

		/**
		 * Checks if Given Plugin version is Greater Than Or Equal To Plugin's Version.
		 *
		 * @param $plugin_file
		 * @param $version
		 *
		 * @static
		 * @return bool
		 */
		public static function version_gte( $plugin_file, $version ) {
			return ( false !== self::version( $plugin_file ) && version_compare( self::version( $plugin_file ), $version, '>=' ) );
		}

		/**
		 * Checks if Given Plugin version is Greater Than To Plugin's Version.
		 *
		 * @param $plugin_file
		 * @param $version
		 *
		 * @static
		 * @return bool
		 */
		public static function version_gt( $plugin_file, $version ) {
			return ( false !== self::version( $plugin_file ) && version_compare( self::version( $plugin_file ), $version, '>' ) );
		}

		/**
		 * Checks if Given Plugin version is Less Than Or Equal To Plugin's Version.
		 *
		 * @param $plugin_file
		 * @param $version
		 *
		 * @static
		 * @return bool
		 */
		public static function version_lte( $plugin_file, $version ) {
			return ( false !== self::version( $plugin_file ) && version_compare( self::version( $plugin_file ), $version, '<=' ) );
		}

		/**
		 * Checks if Given Plugin version is Greater Than To Plugin's Version.
		 *
		 * @param $plugin_file
		 * @param $version
		 *
		 * @static
		 * @return bool
		 */
		public static function version_lt( $plugin_file, $version ) {
			return ( false !== self::version( $plugin_file ) && version_compare( self::version( $plugin_file ), $version, '<' ) );
		}
	}

}