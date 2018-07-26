<?php
/**
 *
 * Project : vsp-framework
 * Date : 25-07-2018
 * Time : 05:24 PM
 * File : class-system-status-report.php
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @package vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'VSP_System_Status_Report' ) ) {
	/**
	 * Class VSP_System_Status_Report
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_System_Status_Report {
		/**
		 * data
		 *
		 * @var array
		 */
		protected static $data = array();

		/**
		 * Gets Current Host Info
		 *
		 * @return bool|string
		 */
		private static function get_host() {
			$host = false;
			if ( defined( 'WPE_APIKEY' ) ) {
				$host = __( 'WP Engine' );
			} elseif ( defined( 'PAGELYBIN' ) ) {
				$host = __( 'Pagely' );
			} elseif ( DB_HOST === 'localhost:/tmp/mysql5.sock' ) {
				$host = __( 'ICDSoft' );
			} elseif ( DB_HOST === 'mysqlv5' ) {
				$host = __( 'NetworkSolutions' );
			} elseif ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
				$host = __( 'iPage' );
			} elseif ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
				$host = __( 'IPower' );
			} elseif ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
				$host = __( 'MediaTemple Grid' );
			} elseif ( strpos( DB_HOST, '.pair.com' ) !== false ) {
				$host = __( 'Pair Networks' );
			} elseif ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
				$host = __( 'Rackspace Cloud' );
			} elseif ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
				$host = __( 'SysFix.eu Power Hosting' );
			} elseif ( false !== strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) ) {
				$host = __( 'Flywheel' );
			} else {
				$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
			}
			return $host;
		}

		/**
		 * @return array|mixed|void
		 * @static
		 */
		public static function setup() {
			if ( ! empty( self::$data ) ) {
				self::$data = apply_filters( 'vsp_system_status_data', self::$data );
				return self::$data;
			}

			global $wpdb;

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$active_theme                             = wp_get_theme();
			$muplugins                                = wp_get_mu_plugins();
			$plugins                                  = get_plugins();
			$active_plugins                           = get_option( 'active_plugins', array() );
			self::$data[ __( 'WordPress' ) ]          = array(
				__( 'Home URL' )              => home_url(),
				__( 'Site URL' )              => site_url(),
				__( 'WP Version' )            => get_bloginfo( 'version' ),
				__( 'WP Debug' )              => self::is_debug(),
				__( 'WP Language' )           => ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ),
				__( 'WP Multisite' )          => is_multisite(),
				__( 'WP Memory Limit' )       => WP_MEMORY_LIMIT . 'MB',
				__( 'WP Table Prefix' )       => $wpdb->prefix,
				__( 'WP Timezone' )           => get_option( 'timezone_string' ) . ', GMT : ' . get_option( 'gmt_offset' ),
				__( 'Permalink Structure' )   => get_option( 'permalink_structure' ),
				__( 'Registered Post Stati' ) => get_post_stati(),
			);
			self::$data[ __( 'Server Information' ) ] = array(
				__( 'Server Info' )      => isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '',
				__( 'Host' )             => self::get_host(),
				__( 'MySql Version' )    => ( $wpdb->use_mysqli ) ? @mysqli_get_server_info( $wpdb->dbh ) : @mysql_get_server_info(),
				__( 'PHP Version' )      => PHP_VERSION,
				__( 'Default Timezone' ) => date_default_timezone_get(),
			);
			self::$data[ __( 'PHP Information' ) ]    = array(
				__( 'PHP Post Max Size' )       => ini_get( 'post_max_size' ),
				__( 'PHP Time Limit' )          => ini_get( 'max_execution_time' ),
				__( 'PHP Max Input Vars' )      => ini_get( 'max_input_vars' ),
				__( 'PHP Safe Mode' )           => ini_get( 'safe_mode' ),
				__( 'PHP Memory Limit' )        => ini_get( 'memory_limit' ),
				__( 'PHP Upload Max Size' )     => ini_get( 'upload_max_filesize' ),
				__( 'PHP Upload Max Filesize' ) => ini_get( 'upload_max_filesize' ),
				__( 'PHP Arg Separator' )       => ini_get( 'arg_separator.output' ),
				__( 'PHP Allow URL File Open' ) => ini_get( 'allow_url_fopen' ),
				__( 'DISPLAY ERRORS' )          => ini_get( 'display_errors' ),
				__( 'FSOCKOPEN' )               => ( function_exists( 'fsockopen' ) ),
				__( 'cURL' )                    => ( function_exists( 'curl_init' ) ),
				__( 'SOAP Client' )             => ( class_exists( 'SoapClient' ) ),
				__( 'SUHOSIN' )                 => ( extension_loaded( 'suhosin' ) ),
				__( 'Session' )                 => isset( $_SESSION ),
				__( 'Session Name' )            => esc_html( ini_get( 'session.name' ) ),
				__( 'Cookie Path' )             => esc_html( ini_get( 'session.cookie_path' ) ),
				__( 'Save Path' )               => esc_html( ini_get( 'session.save_path' ) ),
				__( 'Use Cookies' )             => ini_get( 'session.use_cookies' ),
				__( 'Use Only Cookies' )        => ini_get( 'session.use_only_cookies' ),
			);
			self::$data[ __( 'Active Theme' ) ]       = array(
				__( 'Theme Name' )       => $active_theme->Name,
				__( 'Theme Version' )    => $active_theme->Version,
				__( 'Theme Author' )     => $active_theme->get( 'Author' ),
				__( 'Theme Author URI' ) => $active_theme->get( 'AuthorURI' ),
				__( 'Is Child Theme' )   => is_child_theme(),
			);
			self::$data[ __( 'Parent Theme' ) ]       = array();
			self::$data[ __( 'MustUse Plugins' ) ]    = array();
			self::$data[ __( 'Plugins' ) ]            = array();
			self::$data[ __( 'Active Plugins' ) ]     = array();
			self::$data[ __( 'Multisite Plugins' ) ]  = array();
			
			if ( is_child_theme() ) {
				$parent_theme                       = wp_get_theme( $active_theme->Template );
				$pt                                 = array();
				$pt[ __( 'Parent Theme' ) ]         = $parent_theme->Name;
				$pt[ __( 'Parent Theme Version' ) ] = $parent_theme->Version;
				$pt[ __( 'Parent URI' ) ]           = $parent_theme->get( 'ThemeURI' );
				$pt[ __( 'Parent Author URI' ) ]    = $parent_theme->{'Author URI'};
				self::$data[ __( 'Parent Theme' ) ] = $pt;
			}

			if ( is_array( $muplugins ) && ! empty( $muplugins ) ) {
				foreach ( $muplugins as $plugin ) {
					self::$data[ __( 'MustUse Plugins' ) ][] = $plugin['Name'] . ': By ' . $plugin['Author'] . ' - ' . $plugin['Version'];
				}
			}

			if ( is_array( $plugins ) && ! empty( $plugins ) ) {
				foreach ( $plugins as $plugin_path => $plugin ) {
					if ( ! in_array( $plugin_path, $active_plugins ) ) {
						self::$data[ __( 'Plugins' ) ][] = $plugin['Name'] . ': By ' . $plugin['Author'] . ' - ' . $plugin['Version'];
					} else {
						self::$data[ __( 'Active Plugins' ) ][] = $plugin['Name'] . ': By ' . $plugin['Author'] . ' - ' . $plugin['Version'];
					}
				}
			}

			if ( is_multisite() ) {
				$plugins        = wp_get_active_network_plugins();
				$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				foreach ( $plugins as $plugin_path ) {
					$plugin_base = plugin_basename( $plugin_path );
					if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
						continue;
					}

					$plugin                                    = get_plugin_data( $plugin_path );
					self::$data[ __( 'Multisite Plugins' ) ][] = $plugin['Name'] . ': By ' . $plugin['Author'] . ' - ' . $plugin['Version'];
				}
			}

			self::$data = apply_filters( 'vsp_system_status_data', self::$data );
		}

		/**
		 * @return bool|string
		 * @static
		 */
		public static function is_debug() {
			if ( self::is_defined( 'WP_DEBUG' ) && self::is_defined( 'WP_DEBUG_LOG' ) && self::is_defined( 'WP_DEBUG_DISPLAY' ) ) {
				return true;
			} elseif ( self::is_defined( 'WP_DEBUG' ) && self::is_defined( 'WP_DEBUG_LOG' ) ) {
				return 'WP_DEBUG && WP_DEBUG_LOG';
			} elseif ( self::is_defined( 'WP_DEBUG' ) && self::is_defined( 'WP_DEBUG_DISPLAY' ) ) {
				return 'WP_DEBUG && WP_DEBUG_DISPLAY';
			} elseif ( self::is_defined( 'WP_DEBUG' ) ) {
				return 'WP_DEBUG';
			} else {
				return false;
			}
		}

		/**
		 * @param $key
		 *
		 * @return bool
		 * @static
		 */
		public static function is_defined( $key ) {
			return ( true === defined( $key ) && true === constant( $key ) );
		}

		/**
		 * @return string
		 * @static
		 */
		public static function output() {
			self::setup();
			return wponion_add_element( array(
				'type'    => 'content',
				'content' => '<pre>' . print_r( self::$data, true ) . '</pre>',
			) );
		}

		/**
		 * @return mixed
		 * @static
		 */
		public static function text_output() {
			self::setup();
			return print_r( self::$data, true );
		}
	}
}
