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

global $vsp_plugins;
$vsp_plugins = array();

/**
 * @uses \VSP\Autoloader::load()
 */
spl_autoload_register( '\VSP\Autoloader::load' );

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
		if ( true === $is_url ) {
			return VSP_URL . $extra;
		}
		return vsp_path( $extra );
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
		if ( true === $url ) {
			return vsp_url( 'assets/js/' . $extra );
		}
		return vsp_path( 'assets/js/' . $extra );
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
		if ( true === $url ) {
			return vsp_url( 'assets/css/' . $extra );
		}
		return vsp_path( 'assets/css/' . $extra );
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
		if ( true === $url ) {
			return vsp_url( 'assets/img/' . $extra );
		}
		return vsp_path( 'assets/img/' . $extra );
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
	 * @example vsp_load_file("mypath/*.php")
	 * @example vsp_load_file("mypath/class-*.php")
	 *
	 * @param $path .
	 *
	 * @return array
	 */
	function vsp_get_file_paths( $path ) {
		return glob( $path );
	}
}

/**
 * WordPress Specific Functions
 */
if ( ! function_exists( 'vsp_is_plugin_active' ) ) {
	/**
	 * Checks if given plugin file is active in WordPress
	 *
	 * @param string $file .
	 *
	 * @return bool
	 */
	function vsp_is_plugin_active( $file = '' ) {
		return \VSP\Helper\Dependencies::active_check( $file );
	}
}

if ( ! function_exists( 'vsp_wc_active' ) ) {
	/**
	 * Checks if woocommerce is active
	 * in current wp instance
	 *
	 * @example if(vsp_wc_active()){echo "Yes";}else{echo "No"}
	 * @return bool
	 */
	function vsp_wc_active() {
		return vsp_is_plugin_active( 'woocommerce/woocommerce.php' );
	}
}

if ( ! function_exists( 'vsp_add_wc_required_notice' ) ) {
	/**
	 * Adds WooCommerce Required Notice
	 *
	 * @param string $plugin_name .
	 */
	function vsp_add_wc_required_notice( $plugin_name = '' ) {
		/* translators: Adds Plugin Name & HTML Tags. */
		$msg = __( '%1$s Requires %2$s WooCommerce %3$s to be installed & activated.', 'vsp-framework' );
		$msg = sprintf( $msg, '<strong>' . $plugin_name . '</strong>', '<strong><i>', '</i></strong>' );
		wponion_error_admin_notice( $msg );
	}
}

vsp_load_file( VSP_PATH . 'includes/functions/*.php' );
