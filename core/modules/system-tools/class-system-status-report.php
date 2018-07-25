<?php
/**
 * VSP WP System Status Report.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 18-04-2018
 * Time 10:27 AM
 *
 * @package vsp-framework/core/modules/system-tools
 * @link http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
		 * last_tab
		 *
		 * @var string
		 */
		protected static $last_tab = 'tab';

		/**
		 * Checks if WP Remote Post Works.
		 *
		 * @return string
		 * @static
		 */
		public static function validate_post() {
			$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', array(
				'sslverify' => false,
				'timeout'   => 60,
				'body'      => array( 'cmd' => '_notify-validate' ),
			) );

			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				return 'wp_remote_post() works';
			} else {
				return 'Error : wp_remote_post() does not work';
			}
		}

		/**
		 * Gets Current Host Info
		 *
		 * @return bool|string
		 */
		private static function get_host() {
			$host = false;
			if ( defined( 'WPE_APIKEY' ) ) {
				$host = 'WP Engine';
			} elseif ( defined( 'PAGELYBIN' ) ) {
				$host = 'Pagely';
			} elseif ( DB_HOST === 'localhost:/tmp/mysql5.sock' ) {
				$host = 'ICDSoft';
			} elseif ( DB_HOST === 'mysqlv5' ) {
				$host = 'NetworkSolutions';
			} elseif ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
				$host = 'iPage';
			} elseif ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
				$host = 'IPower';
			} elseif ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
				$host = 'MediaTemple Grid';
			} elseif ( strpos( DB_HOST, '.pair.com' ) !== false ) {
				$host = 'pair Networks';
			} elseif ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
				$host = 'Rackspace Cloud';
			} elseif ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
				$host = 'SysFix.eu Power Hosting';
			} elseif ( false !== strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) ) {
				$host = 'Flywheel';
			} else {
				$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
			}
			return $host;
		}

		/**
		 * Returns SYS INFO Headers
		 *
		 * @return array
		 * @static
		 */
		public static function get_headers() {
			return apply_filters( 'vsp_system_status_headers', array(
				'wp-env'      => array(
					'name'   => '<i class="fa fa-wordpress"></i> ' . __( 'WordPress Environment' ),
					'childs' => array(
						'themes'  => '<i class="fa fa-paint-brush"></i> ' . __( 'Theme Info' ),
						'plugins' => array(
							'name'   => __( 'Plugins Information' ),
							'icon'   => 'fa fa-plug',
							'childs' => array(
								'muse-plugins'      => __( 'Must Use Plugins' ),
								'active-plugins'    => __( 'Active Plugins' ),
								'installed-plugins' => __( 'Installed Plugins' ),
								'multisite-plugins' => __( 'MultiSite Plugins' ),
							),
						),
					),
				),
				'server-env'  => array(
					'name'   => '<i class="fa fa-server"></i> ' . __( 'Server Environment' ),
					'childs' => array(
						'php-info' => '<i class="fa fa-info"></i> ' . __( 'PHP Info' ),
						'php-exts' => '<i class="fa fa-puzzle-piece "></i> ' . __( 'PHP Extentions' ),
						'session'  => '<i class="fa fa-sun-o "></i> ' . __( 'Session Configuration' ),
					),
				),
				'vsp-plugins' => array(
					'name'   => __( 'VSP Plugins' ),
					'childs' => apply_filters( 'vsp_system_status_headers_vsp_plugins', array() ),
				),
				#'client-details' => __( 'Client Details' ),
			) );
		}

		/**
		 * show_on_front
		 *
		 * @return array
		 * @static
		 */
		public static function show_on_front() {
			$r = array();
			if ( get_option( 'show_on_front' ) == 'page' ) {
				$fi                            = get_option( 'page_on_front' );
				$bi                            = get_option( 'page_for_posts' );
				$r[ __( 'Page On Front : ' ) ] = ( 0 !== $fi ? get_the_title( $fi ) . ' (#' . $fi . ')' : 'Unset' );
				$r[ __( 'Page For Posts:' ) ]  = ( 0 !== $bi ? get_the_title( $bi ) . ' (#' . $bi . ')' : 'Unset' );
			}
			return $r;
		}

		/**
		 * Returns Plugin Information
		 *
		 * @param array|object $plugin .
		 *
		 * @return array
		 */
		private static function get_plugin_information( $plugin ) {
			if ( isset( $plugin['Description'] ) ) {
				unset( $plugin['Description'] );
			}
			$html_output = sprintf( '<a href="%s" title="%s">%s</a>', $plugin['PluginURI'], $plugin['Name'], $plugin['Name'] );
			$html_output .= ' | ' . sprintf( __( 'By %s' ), '<a href="' . $plugin['AuthorURI'] . '">' . $plugin['Author'] . '</a>' );
			$html_output .= ' | ' . sprintf( __( 'Version %s' ), $plugin['Version'] );

			return array(
				'html_output' => 'string',
				'data'        => $plugin,
				'html_data'   => $html_output,
			);
		}

		/**
		 * Setup Based Data.
		 *
		 * @return mixed
		 * @static
		 */
		public static function setup_data() {
			if ( ! empty( self::$data ) ) {
				self::$data = apply_filters( 'vsp_system_status_data', self::$data );
				return self::$data;
			}

			global $wpdb;

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$active_theme                    = wp_get_theme();
			$muplugins                       = wp_get_mu_plugins();
			$plugins                         = get_plugins();
			$active_plugins                  = get_option( 'active_plugins', array() );
			self::$data['wp-env']            = array(
				__( 'Home URL' )               => home_url(),
				__( 'Site URL' )               => site_url(),
				__( 'WP Version' )             => get_bloginfo( 'version' ),
				__( 'WP_DEBUG' )               => defined( 'WP_DEBUG' ),
				__( 'WP_DEBUG_DISPLAY' )       => defined( 'WP_DEBUG_DISPLAY' ),
				__( 'WP_DEBUG_LOG' )           => defined( 'WP_DEBUG_LOG' ),
				__( 'WP Language' )            => ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ),
				__( 'WP Multisite' )           => is_multisite(),
				__( 'WP Memory Limit' )        => WP_MEMORY_LIMIT . 'MB',
				__( 'WP Table Prefix' )        => $wpdb->prefix,
				__( 'WP Table Prefix Lenght' ) => strlen( $wpdb->prefix ),
				__( 'WP Table Prefix Status' ) => ( strlen( $wpdb->prefix ) > 16 ) ? __( 'Error : Too Long' ) : __( 'Ok' ),
				__( 'WP Timezone' )            => get_option( 'timezone_string' ) . ', GMT : ' . get_option( 'gmt_offset' ),
				#__( 'WP Remote Post' )         => self::validate_post(),
				__( 'Permalink Structure' )    => get_option( 'permalink_structure' ),
				__( 'Registered Post Stati' )  => get_post_stati(),
				__( 'Show On Front' )          => get_option( 'show_on_front' ),
				__( 'Show On Front Info' )     => self::show_on_front(),

			);
			self::$data['themes']            = array(
				__( 'Theme Name' )       => $active_theme->Name,
				__( 'Theme Version' )    => $active_theme->Version,
				__( 'Theme Author' )     => $active_theme->get( 'Author' ),
				__( 'Theme Author URI' ) => $active_theme->get( 'AuthorURI' ),
				__( 'Is Child Theme' )   => is_child_theme(),

			);
			self::$data['active-plugins']    = array();
			self::$data['installed-plugins'] = array();
			self::$data['muse-plugins']      = array();
			self::$data['multisite-plugins'] = array();

			if ( is_child_theme() ) {
				$parent_theme = wp_get_theme( $active_theme->Template );

				self::$data['themes'][ __( 'Parent Theme' ) ]         = $parent_theme->Name;
				self::$data['themes'][ __( 'Parent Theme Version' ) ] = $parent_theme->Version;
				self::$data['themes'][ __( 'Parent URI' ) ]           = $parent_theme->get( 'ThemeURI' );
				self::$data['themes'][ __( 'Parent Author URI' ) ]    = $parent_theme->{'Author URI'};
			}


			/**
			 * Must Use Plugins Information.
			 */
			if ( count( $muplugins > 0 ) ) {
				foreach ( $muplugins as $plugin_path => $plugin ) {
					self::$data['muse-plugins'][ $plugin_path ] = self::get_plugin_information( $plugin );
				}
			}

			/**
			 * Active And Inactive Plugin Information.
			 */
			foreach ( $plugins as $plugin_path => $plugin ) {
				if ( ! in_array( $plugin_path, $active_plugins ) ) {
					self::$data['installed-plugins'][ $plugin_path ] = self::get_plugin_information( $plugin );
				} else {
					self::$data['active-plugins'][ $plugin_path ] = self::get_plugin_information( $plugin );
				}
			}

			/**
			 * Multisite Plugin Informations.
			 */
			if ( is_multisite() ) {
				$plugins        = wp_get_active_network_plugins();
				$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
				foreach ( $plugins as $plugin_path ) {
					$plugin_base = plugin_basename( $plugin_path );
					if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
						continue;
					}
					$plugin = get_plugin_data( $plugin_path );

					self::$data['multisite-plugins'][ $plugin_base ] = self::get_plugin_information( $plugin );
				}
			}

			self::$data['server-env'] = array(
				__( 'Server Info' )      => $_SERVER['SERVER_SOFTWARE'],
				__( 'Host' )             => self::get_host(),
				__( 'Default Timezone' ) => date_default_timezone_get(),
				__( 'MySQL Version' )    => ( $wpdb->use_mysqli ) ? @mysqli_get_server_info( $wpdb->dbh ) : @mysql_get_server_info(),
			);
			self::$data['php-info']   = array(
				__( 'PHP Version' )             => PHP_VERSION,
				__( 'PHP Post Max Size' )       => ini_get( 'post_max_size' ),
				__( 'PHP Time Limit' )          => ini_get( 'max_execution_time' ),
				__( 'PHP Max Input Vars' )      => ini_get( 'max_input_vars' ),
				__( 'PHP Safe Mode' )           => ini_get( 'safe_mode' ),
				__( 'PHP Memory Limit' )        => ini_get( 'memory_limit' ),
				__( 'PHP Upload Max Size' )     => ini_get( 'upload_max_filesize' ),
				__( 'PHP Upload Max Filesize' ) => ini_get( 'upload_max_filesize' ),
				__( 'PHP Arg Separator' )       => ini_get( 'arg_separator.output' ),
				__( 'PHP Allow URL File Open' ) => ini_get( 'allow_url_fopen' ),
			);
			self::$data['php-exts']   = array(
				__( 'DISPLAY ERRORS' ) => ini_get( 'display_errors' ),
				__( 'FSOCKOPEN' )      => ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.',
				__( 'cURL' )           => ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.',
				__( 'SOAP Client' )    => ( class_exists( 'SoapClient' ) ) ? 'Your server has the SOAP Client enabled.' : 'Your server does not have the SOAP Client enabled.',
				__( 'SUHOSIN' )        => ( extension_loaded( 'suhosin' ) ) ? 'Your server has SUHOSIN installed.' : 'Your server does not have SUHOSIN installed.',
			);
			self::$data['session']    = array(
				__( 'Session' )          => isset( $_SESSION ) ? 'Enabled' : 'Disabled',
				__( 'Session Name' )     => esc_html( ini_get( 'session.name' ) ),
				__( 'Cookie Path' )      => esc_html( ini_get( 'session.cookie_path' ) ),
				__( 'Save Path' )        => esc_html( ini_get( 'session.save_path' ) ),
				__( 'Use Cookies' )      => ini_get( 'session.use_cookies' ),
				__( 'Use Only Cookies' ) => ini_get( 'session.use_only_cookies' ),
			);

			self::$data = apply_filters( 'vsp_system_status_data', self::$data );
			return self::$data;
		}

		/**
		 * @return mixed
		 * @static
		 */
		public static function text_output() {
			$data    = self::setup_data();
			$headers = self::get_headers();
			return self::text_output_content( $headers );
		}

		/**
		 * @param $headers
		 *
		 * @return string
		 * @static
		 */
		public static function text_output_content( $headers, $in_level = false ) {
			$return = '';

			foreach ( $headers as $slug => $header ) {
				$title = $header;
				if ( is_array( $header ) && isset( $header['name'] ) ) {
					$title = strip_tags( $header['name'] );
				}
				$content = false;
				if ( isset( self::$data[ $slug ] ) ) {
					$content = self::render_text( self::$data[ $slug ] );
				}

				if ( ( false === $content || empty( $content ) ) && ! isset( $header['childs'] ) ) {
					continue;
				}

				$return .= PHP_EOL;
				$return .= ( false === $in_level ) ? '##' : '###';
				$return .= strip_tags( $title );
				$return .= ( false === $in_level ) ? '##' : '###';
				$return .= PHP_EOL;
				$return .= ( false !== $content ) ? $content . PHP_EOL : '';
				$return .= ( isset( $header['childs'] ) ) ? self::text_output_content( $header['childs'], $in_level + 1 ) : '';
				$return .= ( false === $in_level ) ? '---' : '';
			}
			return $return;
		}

		/**
		 * @param $data
		 *
		 * @return string
		 * @static
		 */
		public static function render_text( $data ) {
			$return = array();
			if ( ! empty( $data ) ) {
				foreach ( $data as $title => $_data ) {
					$return[] = '* ' . $title . ' : ' . self::handle_result_text( $_data );
				}
			}
			return implode( PHP_EOL, $return );
		}

		/**
		 * Outputs HTML.
		 *
		 * @static
		 */
		public static function output() {
			$headers = self::get_headers();
			$data    = self::setup_data();
			self::wpsf_html_output( $headers );
		}

		/**
		 * @param $slug
		 *
		 * @return array
		 * @static
		 */
		public static function wpsf_content_fields( $slug ) {
			return array(
				array(
					'type'    => 'content',
					'content' => self::render_table( $slug ),
				),
			);
		}

		/**
		 * @param $headers
		 *
		 * @return array
		 * @static
		 */
		public static function wpsf_child_render_sections( $headers ) {
			$return = array();
			foreach ( $headers as $slug => $data ) {
				if ( is_array( $data ) ) {
					$icon            = ( isset( $data['icon'] ) ) ? '<i class="' . $data['icon'] . '"></i>' : '';
					$return[ $slug ] = array(
						'type'            => 'accordion',
						'open'            => true,
						'accordion_title' => $icon . ' ' . $data['name'],
						'fields'          => array(
							array(
								'id'       => $slug . '_vsp_sys_info',
								'type'     => 'tab',
								'sections' => array(),
							),
						),
					);

					$content = self::wpsf_content_fields( $slug );
					if ( ! empty( $content[0]['content'] ) ) {
						$return[ $slug ]['fields'][0]['sections'][] = $content;
					}

					$return[ $slug ]['fields'][0]['sections'] = array_merge( $return[ $slug ]['fields'][0]['sections'], self::wpsf_render_sections( $data['childs'] ) );
				} else {
					$content = self::wpsf_content_fields( $slug );
					if ( ! empty( $content[0]['content'] ) ) {
						$return[ $slug ] = array(
							'type'            => 'accordion',
							'open'            => true,
							'accordion_title' => $data,
							'fields'          => $content,
						);
					}
				}
			}
			return $return;
		}

		/**
		 * @param $headers
		 *
		 * @return array
		 * @static
		 */
		public static function wpsf_render_sections( $headers ) {
			$return = array();
			foreach ( $headers as $slug => $data ) {
				if ( is_array( $data ) ) {
					$return[ $slug ] = array(
						'name'   => $slug,
						'title'  => $data['name'],
						'icon'   => isset( $data['icon'] ) ? $data['icon'] : '',
						'fields' => array(),
					);

					$content = self::wpsf_content_fields( $slug );
					if ( ! empty( $content[0]['content'] ) ) {
						$return[ $slug ]['fields'][] = array(
							'type'            => 'accordion',
							'open'            => true,
							'accordion_title' => $data['name'],
							'fields'          => $content,
						);
					}

					$return[ $slug ]['fields'] = array_merge( $return[ $slug ]['fields'], self::wpsf_child_render_sections( $data['childs'] ) );
				} else {
					$content = self::wpsf_content_fields( $slug );

					if ( ! empty( $content[0]['content'] ) ) {
						$return[ $slug ] = array(
							'name'   => $slug,
							'title'  => $data,
							'fields' => $content,
						);
					}
				}
			}
			return $return;
		}

		/**
		 * @param $headers
		 *
		 * @static
		 */
		public static function wpsf_html_output( $headers ) {
			$wpsf_fields = array(
				'id'       => 'vsp_sys_info',
				'type'     => 'tab',
				'sections' => self::wpsf_render_sections( $headers ),
			);
			echo wponion_add_element( $wpsf_fields, false, '_' );
		}

		/**
		 * @param $result
		 *
		 * @return false|mixed|string
		 * @static
		 */
		public static function handle_result_html( $result ) {
			if ( is_bool( $result ) ) {
				return ( true === $result ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';
			} elseif ( is_array( $result ) ) {
				if ( isset( $result['html_output'] ) && ( isset( $result['data'] ) || isset( $result['html_data'] ) ) ) {
					$data = ( isset( $result['html_data'] ) ) ? $result['html_data'] : $result['data'];
					if ( in_array( $result['html_output'], array( 'ul', 'ol' ) ) ) {
						$html = '<' . $result['html_output'] . '>';
						foreach ( $data as $key => $value ) {
							$html .= '<li>';
							if ( ! is_numeric( $key ) ) {
								$html .= '<strong>' . $key . ' : </strong>';
							}
							$html .= self::handle_result_html( $value );
							$html .= '</li>';
						}
						$html .= '</' . $result['html_output'] . '>';
						return $html;
					} elseif ( 'json' === $result['html_output'] ) {
						return wp_json_encode( $result['data'] );
					} elseif ( 'table' === $result['html_output'] ) {
						return self::render_value_table( $data );
					} elseif ( 'string' === $result['html_output'] ) {
						return $data;
					}
				}

				return '<pre>' . print_r( $result, true ) . '</pre>';
			}
			return $result;
		}

		/**
		 * @param $result
		 *
		 * @return mixed|string
		 * @static
		 */
		public static function handle_result_text( $result ) {
			if ( is_bool( $result ) ) {
				if ( true === $result ) {
					return '&#10004;';
				}
				return '&#10060;';
			} elseif ( is_array( $result ) ) {
				$r = PHP_EOL . '```php' . PHP_EOL;
				$r .= var_export( $result, true );
				$r .= PHP_EOL . '```' . PHP_EOL;
				return $r;
			}

			return $result;
		}

		/**
		 * @param $data
		 *
		 * @return string
		 * @static
		 */
		public static function render_value_table( $data ) {
			$return = '<table class="vsp_sys_report_table widefat striped">';
			foreach ( $data as $title => $d ) {
				$return .= '<tr>';
				if ( ! is_numeric( $title ) ) {
					$return .= '<th>' . $title . ' : </th>';
				}
				$return .= '<td>' . self::handle_result_html( $d ) . '</td>';
				$return .= '</tr>';
			}
			$return .= '</table>';
			return $return;
		}

		/**
		 * @param $slug
		 *
		 * @return string
		 * @static
		 */
		public static function render_table( $slug ) {
			if ( ! isset( self::$data[ $slug ] ) || empty( self::$data[ $slug ] ) ) {
				return '';
			}

			$return = '<table id="vsp_sys_status_' . sanitize_html_class( $slug ) . '" class="vsp_sys_report_table widefat striped fixed">';
			foreach ( self::$data[ $slug ] as $title => $data ) {
				$return .= '<tr>';
				$return .= '<th>' . $title . ' : </th><td>' . self::handle_result_html( $data ) . '</td>';
				$return .= '</tr>';
			}
			$return .= '</table>';
			return $return;
		}
	}
}
