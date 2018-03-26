<?php
/**
 * Class to render Status Report
 *
 * @package vsp-framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Status_Report' ) ) {
	/**
	 * Class VSP_Status_Report
	 */
	class VSP_Status_Report {

		/**
		 * Instance
		 *
		 * @var null
		 */
		private static $_instance = null;

		/**
		 * Creates A Instance of VSP_Status_Report
		 *
		 * @return null|\VSP_Status_Report
		 */
		public static function instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * VSP_Status_Report constructor.
		 */
		public function __construct() {
			$this->wp_env           = array();
			$this->active_theme     = array();
			$this->must_use_plugins = array();
			$this->active_plugins   = array();
			$this->msite_plugins    = array();
			$this->server_info      = array();
			$this->php_info         = array();
			$this->php_exts         = array();
			$this->session_config   = array();
			$this->vsp_framework    = array();
			$this->vsp_settings     = array();
			$this->set_wp_env_data();
			$this->set_active_theme_data();
			$this->set_plugins_data();
			$this->set_php_data();
			$this->set_vsp_framework();
			/**
			 * Issue with VSP Settings Rewrite
			 *
			 * @todo need to rewrite vsp settings function
			 * $this->set_vsp_settings();
			 */
			$this->output_html();
		}

		/**
		 * Checks for Bool
		 *
		 * @param boolean|bool|string $check   .
		 * @param string              $success .
		 * @param string              $fail    .
		 *
		 * @return string
		 */
		private function __bool( $check, $success = 'Enabled', $fail = 'Disabled' ) {
			if ( true === $check ) {
				return $success;
			}
			return $fail;
		}

		/**
		 * Returns Theme Information
		 *
		 * @param  array|object $theme  .
		 * @param  string       $prefix .
		 *
		 * @return array
		 */
		private function get_theme_information( $theme, $prefix = '' ) {
			$return = array();

			$return[ $prefix . __( 'Theme Name', 'vsp-framework' ) ]        = $theme->name;
			$return[ $prefix . __( 'Theme Version', 'vsp-framework' ) ]     = $theme->version;
			$return[ $prefix . __( 'Theme URI', 'vsp-framework' ) ]         = $theme->get( 'ThemeURI' );
			$return[ $prefix . __( 'Theme Description', 'vsp-framework' ) ] = $theme->description;
			$return[ $prefix . __( 'Theme Author', 'vsp-framework' ) ]      = $theme->get( 'Author' );
			$return[ $prefix . __( 'Theme AuthorURI', 'vsp-framework' ) ]   = $theme->get( 'AuthorURI' );
			$return[ $prefix . __( 'Parent Theme', 'vsp-framework' ) ]      = $theme->parent_theme;
			return $return;
		}

		/**
		 * Returns Plugin Information
		 *
		 * @param array|object $plugin .
		 * @param string       $prefix .
		 *
		 * @return array
		 */
		private function get_plugin_information( $plugin, $prefix = '' ) {
			$return = array();

			$return[ $prefix . __( 'Name', 'vsp-framework' ) ]      = $plugin['Name'];
			$return[ $prefix . __( 'PluginURI', 'vsp-framework' ) ] = $plugin['PluginURI'];
			$return[ $prefix . __( 'Version', 'vsp-framework' ) ]   = $plugin['Version'];
			$return[ $prefix . __( 'Author', 'vsp-framework' ) ]    = $plugin['Author'];
			$return[ $prefix . __( 'AuthorURI', 'vsp-framework' ) ] = $plugin['AuthorURI'];
			return $return;
		}

		/**
		 * Gets Current Host Info
		 *
		 * @return bool|string
		 */
		private function get_host() {
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
		 * Sets WP Env Info
		 */
		public function set_wp_env_data() {
			global $wpdb, $WP_REMOTE_POST;
			$front_page_id = get_option( 'page_on_front' );
			$blog_page_id  = get_option( 'page_for_posts' );

			$this->wp_env[ __( 'Home URL', 'vsp-framework' ) ]              = home_url();
			$this->wp_env[ __( 'Site URL', 'vsp-framework' ) ]              = site_url();
			$this->wp_env[ __( 'WP Version', 'vsp-framework' ) ]            = get_bloginfo( 'version' );
			$this->wp_env[ __( 'WP Language', 'vsp-framework' ) ]           = ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' );
			$this->wp_env[ __( 'Multisite', 'vsp-framework' ) ]             = $this->__bool( is_multisite() );
			$this->wp_env[ __( 'WP Memory Limit', 'vsp-framework' ) ]       = WP_MEMORY_LIMIT . "MB";
			$this->wp_env[ __( 'WP Table Prefix', 'vsp-framework' ) ]       = $wpdb->prefix;
			$this->wp_env[ __( 'WP Timezone', 'vsp-framework' ) ]           = get_option( 'timezone_string' ) . ', GMT: ' . get_option( 'gmt_offset' );
			$this->wp_env[ __( 'WP Remote Post', 'vsp-framework' ) ]        = ( $WP_REMOTE_POST ) ? __( 'Enabled', 'vsp-framework' ) : __( 'Disabled', 'vsp-framework' );
			$this->wp_env[ __( 'Permalink Structure', 'vsp-framework' ) ]   = get_option( 'permalink_structure' );
			$this->wp_env[ __( 'Registered Post Stati', 'vsp-framework' ) ] = implode( " , ", get_post_stati() );
			$this->wp_env[ __( 'Show On Front', 'vsp-framework' ) ]         = get_option( 'show_on_front' );
			$this->wp_env[ __( 'Page On Front', 'vsp-framework' ) ]         = ( $front_page_id !== 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' );
			$this->wp_env[ __( 'Show On Front', 'vsp-framework' ) ]         = ( $blog_page_id !== 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' );
			$this->server_info[ __( 'Server Info', 'vsp-framework' ) ]      = $_SERVER['SERVER_SOFTWARE'];
			$this->server_info[ __( 'Host', 'vsp-framework' ) ]             = $this->get_host();
			$this->server_info[ __( 'Default Timezone', 'vsp-framework' ) ] = date_default_timezone_get();

			if ( $wpdb->use_mysqli ) {
				$this->server_info[ __( 'MySql Version', 'vsp-framework' ) ] = @mysqli_get_server_info( $wpdb->dbh );
			} else {
				$this->server_info[ __( 'MySql Version', 'vsp-framework' ) ] = @mysql_get_server_info();
			}
		}

		/**
		 * Sets Active Theme Info
		 */
		public function set_active_theme_data() {
			$active_theme = wp_get_theme();
			$active_theme = $this->get_theme_information( $active_theme );

			$active_theme[ __( 'Is Child Theme', 'vsp-framework' ) ] = is_child_theme() ? __( 'Yes', 'vsp-framework' ) : __( 'No', 'vsp-framework' );

			if ( is_child_theme() ) {
				$parent_theme = wp_get_theme( $active_theme->Template );
				$active_theme = array_merge( $active_theme, $this->get_theme_information( $parent_theme, __( 'Parent Theme', 'vsp-framework' ) ) );
			}

			$this->active_theme = $active_theme;
		}

		/**
		 * Sets Plugin Information
		 */
		public function set_plugins_data() {
			$muplugins = wp_get_mu_plugins();

			if ( ! empty( $muplugins ) ) {
				foreach ( $muplugins as $plugin => $plugin_data ) {
					$this->must_use_plugins[ $plugin['Name'] ] = $this->get_plugin_information( $plugin_data );
				}
			}

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins        = get_plugins();
			$active_plugins = get_option( 'active_plugins', array() );

			foreach ( $plugins as $plugin_path => $plugin ) {
				if ( in_array( $plugin_path, $active_plugins ) ) {
					continue;
				}
				$this->active_plugins[ $plugin_path ] = $this->get_plugin_information( $plugin );
			}

			if ( is_multisite() ) {
				$plugins        = wp_get_active_network_plugins();
				$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

				foreach ( $plugins as $plugin_path ) {
					$plugin_base = plugin_basename( $plugin_path );
					if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
						continue;
					}
					$this->msite_plugins[ $plugin_path ] = $this->get_plugin_information( get_plugin_data( $plugin ) );
				}
			}
		}

		/**
		 * Sets PHP info
		 */
		public function set_php_data() {
			$this->php_info[ __( 'PHP Version', 'vsp-framework' ) ]             = PHP_VERSION;
			$this->php_info[ __( 'PHP Post Max Size', 'vsp-framework' ) ]       = ini_get( 'post_max_size' );
			$this->php_info[ __( 'PHP Time Limit', 'vsp-framework' ) ]          = ini_get( 'max_execution_time' );
			$this->php_info[ __( 'PHP Max Input Vars', 'vsp-framework' ) ]      = ini_get( 'max_input_vars' );
			$this->php_info[ __( 'PHP Safe Mode', 'vsp-framework' ) ]           = $this->__bool( ini_get( 'safe_mode' ) );
			$this->php_info[ __( 'PHP Memory Limit', 'vsp-framework' ) ]        = ini_get( 'memory_limit' );
			$this->php_info[ __( 'PHP Upload Max Size', 'vsp-framework' ) ]     = ini_get( 'upload_max_filesize' );
			$this->php_info[ __( 'PHP Arg Separator', 'vsp-framework' ) ]       = ini_get( 'arg_separator.output' );
			$this->php_info[ __( 'PHP Allow URL File Open', 'vsp-framework' ) ] = $this->__bool( ini_get( 'allow_url_fopen' ) );
			$this->php_exts[ __( 'Display Errors', 'vsp-framework' ) ]          = $this->__bool( ini_get( 'display_errors' ) );
			$this->php_exts[ __( 'FSOCKOPEN', 'vsp-framework' ) ]               = $this->__bool( function_exists( 'fsockopen' ) );
			$this->php_exts[ __( 'cURL', 'vsp-framework' ) ]                    = $this->__bool( function_exists( 'curl_init' ) );
			$this->php_exts[ __( 'SOAP Client', 'vsp-framework' ) ]             = $this->__bool( function_exists( 'SoapClient' ) );
			$this->php_exts[ __( 'SUHOSIN', 'vsp-framework' ) ]                 = $this->__bool( function_exists( 'suhosin' ) );
			$this->session_config[ __( 'Session', 'vsp-framework' ) ]           = $this->__bool( isset( $_SESSION ) );
			$this->session_config[ __( 'Session Name', 'vsp-framework' ) ]      = esc_html( ini_get( 'session.name' ) );
			$this->session_config[ __( 'Cookie Path', 'vsp-framework' ) ]       = esc_html( ini_get( 'session.cookie_path' ) );
			$this->session_config[ __( 'Save Path', 'vsp-framework' ) ]         = esc_html( ini_get( 'session.save_path' ) );
			$this->session_config[ __( 'Use Cookies', 'vsp-framework' ) ]       = $this->__bool( ini_get( 'session.use_cookies' ), 'Yes', 'No' );
			$this->session_config[ __( 'Use Only Cookies', 'vsp-framework' ) ]  = $this->__bool( ini_get( 'session.use_only_cookies' ), 'Yes', 'No' );
		}

		/**
		 * Sets VSP Framework Info
		 */
		public function set_vsp_framework() {
			$vsp_framework_loader = VSP_Framework_Loader::instance();
			$vsp_loaded_framework = $vsp_framework_loader->loaded();

			$this->vsp_framework[ __( 'Framework Version', 'vsp-framework' ) ]     = $vsp_loaded_framework['Version'];
			$this->vsp_framework[ __( 'Textdomain', 'vsp-framework' ) ]            = $vsp_loaded_framework['TextDomain'];
			$this->vsp_framework[ __( 'DomainPath', 'vsp-framework' ) ]            = $vsp_loaded_framework['DomainPath'];
			$this->vsp_framework[ __( 'Framework Plugin Path', 'vsp-framework' ) ] = str_replace( vsp_unslashit( ABSPATH ), '', $vsp_loaded_framework['plugin_path'] );
			$this->vsp_framework[ __( 'Framework Path', 'vsp-framework' ) ]        = str_replace( vsp_unslashit( ABSPATH ), '', $vsp_loaded_framework['framework_path'] );

			$this->vsp_framework = apply_filters( 'vsp_framework_syspage_framework_info', $this->vsp_framework );
		}

		/**
		 * Sets All Plugin Info that uses VSP
		 */
		public function set_vsp_settings() {
			$active_Plugins = vsp_get_all_plugins( false );

			foreach ( $active_Plugins as $Plugin ) {
				$this->vsp_settings[ $Plugin->plugin_name() ] = vsp_option( $Plugin->slug(), 'all' );
			}
		}

		/**
		 * Returns Main Headings
		 *
		 * @return array
		 */
		public function headings() {
			return apply_filters( 'vsp_sys_status_heading', array(
				'vsp_framework'    => array(
					'name'      => __( 'VSP Framework', 'vsp-framework' ),
					'deep_loop' => true,
				),
				'wp_env'           => array(
					'name'      => __( 'WordPress Environment', 'vsp-framework' ),
					'deep_loop' => false,
				),
				'vsp_settings'     => array(
					'name'      => __( 'VSP Plugin Settings', 'vsp-framework' ),
					'deep_loop' => true,
				),
				'server_info'      => array(
					'name'      => __( 'Server Environment', 'vsp-framework' ),
					'deep_loop' => false,
				),
				'php_info'         => array(
					'name'      => __( 'PHP Environment', 'vsp-framework' ),
					'deep_loop' => false,
				),
				'php_exts'         => array(
					'name'      => __( 'PHP Extenstions', 'vsp-framework' ),
					'deep_loop' => false,
				),
				'session_config'   => array(
					'name'      => __( 'Session Config', 'vsp-framework' ),
					'deep_loop' => false,
				),
				'active_theme'     => array(
					'name'      => __( 'Active Theme', 'vsp-framework' ),
					'deep_loop' => false,
				),
				'active_plugins'   => array(
					'name'      => __( 'Active Plugins', 'vsp-framework' ),
					'deep_loop' => true,
				),
				'msite_plugins'    => array(
					'name'      => __( 'Muilti Site Plugins', 'vsp-framework' ),
					'deep_loop' => true,
				),
				'must_use_plugins' => array(
					'name'      => __( 'WordPress Must Use Plugins', 'vsp-framework' ),
					'deep_loop' => true,
				),
			) );
		}

		/**
		 * Renders SYS Page HTML
		 */
		public function output_html() {
			$heads       = $this->headings();
			$html_output = '';
			$text        = '';
			do_action( 'vsp_sys_status_before_render', $this );
			foreach ( $heads as $var => $_hdata ) {
				$name      = isset( $_hdata['name'] ) ? $_hdata['name'] : rand();
				$is_exists = ( isset( $this->{$var} ) ) ? true : false;
				if ( ! $is_exists ) {
					continue;
				}

				$datas = array_filter( $this->{$var} );
				if ( empty( $datas ) ) {
					continue;
				}

				$text        .= '## ' . $name . ' ##' . PHP_EOL;
				$html_output .= '<section><table  class="widefat striped fixed sectionHeading">';
				$html_output .= '<thead><tr><th colspan="2"><b>' . $name . '</b> <span class="dashicons dashicons-arrow-up"></span></th></tr></thead></table>';
				$html_output .= '<div class="tbody"><table   class="widefat striped fixed">';
				if ( isset( $_hdata['deep_loop'] ) && true === $_hdata['deep_loop'] ) {
					foreach ( $datas as $i => $infos ) {

						if ( is_array( $infos ) ) {
							$i_ap = $i . ': ' . PHP_EOL;
							$i_op = '<table class="striped widefat">';

							foreach ( $infos as $c => $v ) {
								if ( is_array( $v ) ) {
									$v = wp_json_encode( $v );
								}
								if ( is_int( $c ) ) {
									$i_ap .= '   --- ' . $v . PHP_EOL;
									$i_op .= '<tr><td>' . $v . '</td></tr>';
								} else {
									$i_ap .= '   --- ' . $c . ' : ' . $v . PHP_EOL;
									$i_op .= '<tr><th>' . $c . '</th><td>' . $v . '</td></tr>';
								}
							}
							$i_op        .= '</table>';
							$html_output .= '<tr> <th>' . $i . '</th> <td>' . $i_op . '</td></tr>';
							$text        .= $i_ap . PHP_EOL;
						} else {
							if ( is_array( $infos ) ) {
								$infos = wp_json_encode( $infos ) . PHP_EOL;
							}
							$html_output .= '<tr> <th>' . $i . '</th> <td>' . $infos . '</td></tr>';
							$text        .= $i . ' : ' . $infos . PHP_EOL;
						}

					}
				} else {
					foreach ( $datas as $id => $val ) {
						if ( is_array( $val ) ) {
							$val = wp_json_encode( $val ) . PHP_EOL;
						}
						$html_output .= '<tr> <th>' . $id . '</th> <td>' . $val . '</td></tr>';
						$text        .= $id . ' : ' . $val . PHP_EOL;
					}
				}

				$text        .= PHP_EOL;
				$html_output .= '</div></table></section> <br/>';
			}
			$this->text_output = $text;
			$this->html_output = $html_output;
		}

		/**
		 * Final HTML Output
		 *
		 * @return string
		 */
		public function get_output() {
			$title = __( 'To copy the System Status, click below then press Ctrl + C (PC) or Cmd + C (Mac)', 'vsp-framework' );
			$html  = '<div class="updated woocommerce-message inline">';
			$html  .= '<p>' . __( 'Please copy and paste this information in your ticket when contacting support: ', 'vsp-framework' ) . '</p>';
			$html  .= '<textarea style="display:none; min-height:250px; width:100%;" readonly="readonly" onclick="this.focus();this.select()" id="wcqdssstextarea" title="' . $title . '">' . $this->text_output . '</textarea>';
			$html  .= '<p class="submit">';
			$html  .= '<a href="#" class="button-primary debug-report" id="vsp-sys-status-report-text-btn">' . __( 'Get system report', 'vsp-framework' ) . '</a>';
			$html  .= '</p>';
			$html  .= '</div><br/>';
			$html  .= $this->html_output;
			$html  .= ' <script> jQuery(document).ready(function(){ jQuery("table.sectionHeading thead th").on("click",function(){ jQuery(this).parent().parent().parent().parent().find(".tbody").slideToggle(); jQuery(this).find("> span").toggleClass("dashicons-arrow-down"); }) }) </script>';


			return $html;
		}
	}
}
