<?php
/**
 * VSP Framework Required Basic Functions.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'vsp_load_integration' ) ) {
	/**
	 * Loads Given Integration File
	 *
	 * @param string $type Integration Name.
	 *
	 * @return bool
	 */
	function vsp_load_integration( $type = '' ) {
		return \VSP\Autoloader::integration( $type );
	}
}

if ( ! function_exists( 'vsp_load_lib' ) ) {
	/**
	 * Loads Given Library
	 *
	 * @param string $type Lib Name.
	 *
	 * @return bool
	 */
	function vsp_load_lib( $type = '' ) {
		return \VSP\Autoloader::library( $type );
	}
}

if ( ! function_exists( 'vsp_define' ) ) {
	/**
	 * Defines Give Values if not defined
	 *
	 * @param $key .
	 * @param $value .
	 *
	 * @return bool
	 *
	 * @uses \define()
	 */
	function vsp_define( $key, $value ) {
		return defined( $key ) ? define( $key, $value ) : false;
	}
}

if ( ! function_exists( 'vsp_url' ) ) {
	/**
	 * Returns VSP Framework url
	 *
	 * @param string $extra .
	 * @param bool   $is_url .
	 *
	 * @return string
	 */
	function vsp_url( $extra = '', $is_url = true ) {
		return ( true === $is_url ) ? VSP_URL . $extra : vsp_path( $extra );
	}
}

if ( ! function_exists( 'vsp_path' ) ) {
	/**
	 * Returns VSP Framework Full PATH
	 *
	 * @param string $extra .
	 *
	 * @return string
	 */
	function vsp_path( $extra = '' ) {
		return VSP_PATH . $extra;
	}
}

if ( ! function_exists( 'vsp_js' ) ) {
	/**
	 * Returns VSP Framework assets/js Path / URL base on given values
	 *
	 * @param string $extra .
	 * @param bool   $url .
	 *
	 * @return string
	 */
	function vsp_js( $extra = '', $url = true ) {
		return ( true === $url ) ? vsp_url( 'assets/js/' . $extra ) : vsp_path( 'assets/js/' . $extra );
	}
}

if ( ! function_exists( 'vsp_css' ) ) {
	/**
	 * Returns VSP Framework assets/css Path / URL base on given values
	 *
	 * @param string $extra .
	 * @param bool   $url .
	 *
	 * @return string
	 */
	function vsp_css( $extra = '', $url = true ) {
		return ( true === $url ) ? vsp_url( 'assets/css/' . $extra ) : vsp_path( 'assets/css/' . $extra );
	}
}

if ( ! function_exists( 'vsp_img' ) ) {
	/**
	 * Returns VSP Framework assets/img Path / URL base on given values
	 *
	 * @param string $extra .
	 * @param bool   $url .
	 *
	 * @return string
	 */
	function vsp_img( $extra = '', $url = true ) {
		return ( true === $url ) ? vsp_url( 'assets/img/' . $extra ) : vsp_path( 'assets/img/' . $extra );
	}
}

if ( ! function_exists( 'vsp_debug_file' ) ) {
	/**
	 * Makes .min.css / .min.js file based on WordPress config
	 * if WP_DEBUG / SCRIPT_DEBUG is set to true then it loads unminified files
	 *
	 * @param string $filename .
	 * @param bool   $makeurl .
	 * @param bool   $is_url .
	 *
	 * @return mixed|null|string
	 */
	function vsp_debug_file( $filename, $makeurl = false, $is_url = true ) {
		if ( empty( $filename ) ) {
			return null;
		}

		if ( ! ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) ) {
			$filename = str_replace( array( '.min.css', '.min.js' ), array( '.css', '.js' ), $filename );
			$filename = str_replace( '.css', '.min.css', $filename );
			$filename = str_replace( '.js', '.min.js', $filename );
		}

		if ( 'js' === $makeurl ) {
			return vsp_js( $filename, $is_url );
		}

		if ( 'css' === $makeurl ) {
			return vsp_css( $filename, $is_url );
		}

		if ( 'assets' === $makeurl ) {
			return vsp_url( $makeurl . '/' . $filename, $is_url );
		}

		if ( 'url' === $makeurl ) {
			return vsp_url( $filename, $is_url );
		}

		return $filename;
	}
}

if ( ! function_exists( 'vsp_load_file' ) ) {
	/**
	 * Search and loads files based on the search parameter
	 *
	 * @param         $search_type
	 * @param boolean $is_require
	 * @param bool    $once
	 *
	 * @uses    vsp_get_file_paths
	 * @example vsp_load_file("mypath/*.php")
	 * @example vsp_load_file("mypath/class-*.php")
	 */
	function vsp_load_file( $search_type, $is_require = true, $once = false ) {
		foreach ( vsp_get_file_paths( $search_type ) as $files ) {
			if ( $is_require ) {
				if ( $once ) {
					require_once $files;
				} else {
					require $files;
				}
			} else {
				if ( $once ) {
					include_once $files;
				} else {
					include $files;
				}
			}
		}
	}
}

if ( ! function_exists( 'vsp_get_file_paths' ) ) {
	/**
	 * Returns files in a given path
	 *
	 * @param $path .
	 *
	 * @return array
	 * @example vsp_load_file("mypath/*.php")
	 * @example vsp_load_file("mypath/class-*.php")
	 *
	 */
	function vsp_get_file_paths( $path ) {
		return glob( $path );
	}
}

if ( ! function_exists( 'vsp_list_files' ) ) {
	/**
	 * @param       $path
	 * @param int   $levels
	 * @param array $exclusions
	 *
	 * @return bool|string[]
	 */
	function vsp_list_files( $path, $levels = 100, $exclusions = array() ) {
		if ( ! function_exists( 'list_files' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		return list_files( $path, $levels, $exclusions );
	}
}

/**
 * WordPress Specific Functions
 */
if ( ! function_exists( 'vsp_validate_required_plugin' ) ) {
	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	function vsp_validate_required_plugin( $args = array() ) {
		$msg  = false;
		$args = wp_parse_args( $args, array(
			'plugin_name'     => false,// Your Plugin Name.
			'req_plugin'      => false, // Plugin File Eg : woocommerce/woocommerce.php
			'req_plugin_name' => false, // Name of the plugin.
			'version'         => false, // Plugin Version
			'compare'         => 'gte', // Eg : gte,gt.lt,lte
		) );

		if ( ! wp_is_plugin_active( $args['req_plugin'] ) ) {
			$msg = __( '%1$s Requires %2$s to be installed & activated.', 'vsp-framework' );
			$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong><i>' . $args['req_plugin_name'] . '</i></strong>' );
		}

		if ( false !== $args['version'] && false === $msg ) {
			switch ( $args['compare'] ) {
				case 'gte':
				case '>=':
					if ( plugin_version_gte( $args['req_plugin'], $args['version'] ) ) {
						$msg = __( '%1$s Requires %2$s Version %3$s Or Higher. Please Update Your %2$s To %3$s' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;
				case 'gt':
				case '>':
					if ( plugin_version_gt( $args['req_plugin'], $args['version'] ) ) {
						$msg = __( '%1$s Requires %2$s Version %3$s. Please Update Your %2$s To %3$s' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;
				case 'lt':
				case '<':
					if ( plugin_version_lt( $args['req_plugin'], $args['version'] ) ) {
						$msg = __( '%1$s Requires %2$s Version %3$s. Please Downgrade Your %2$s' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;

				case 'lte':
				case '<=':
					if ( plugin_version_lte( $args['req_plugin'], $args['version'] ) ) {
						$msg = __( '%1$s Requires %2$s Version %3$s Or Lower. Please Downgrade Your %2$s To %3$s' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;
			}
		}

		if ( false !== $msg ) {
			if ( ! did_action( 'wponion_loaded' ) ) {
				$add_error = function () use ( $msg ) {
					wponion_error_admin_notice( $msg );
				};
				add_action( 'wponion_loaded', $add_error );
			} else {
				wponion_error_admin_notice( $msg );
			}
		}
		return ( false !== $msg ) ? true : false;
	}
}

if ( ! function_exists( 'vsp_add_wc_required_notice' ) ) {
	/**
	 * @param string $plugin_name
	 * @param string $wc_version
	 * @param string $wc_compare
	 *
	 * @return bool
	 */
	function vsp_add_wc_required_notice( $plugin_name = '', $wc_version = '3.0', $wc_compare = '>=' ) {
		return vsp_validate_required_plugin( array(
			'plugin_name'     => $plugin_name,// Your Plugin Name.
			'req_plugin'      => 'woocommerce/woocommerce.php', // Plugin File Eg : woocommerce/woocommerce.php
			'req_plugin_name' => 'WooCommerce', // Name of the plugin.
			'version'         => $wc_version, // Plugin Version
			'compare'         => $wc_compare, // Eg : gte,gt.lt,lte
		) );

	}
}


vsp_load_file( VSP_PATH . 'includes/functions/*.php' );
