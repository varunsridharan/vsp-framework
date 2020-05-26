<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'vsp_url' ) ) {
	/**
	 * Returns VSP Framework url
	 *
	 * @param string $extra
	 * @param bool   $is_url
	 *
	 * @return string
	 */
	function vsp_url( $extra = '', $is_url = true ) {
		return ( $is_url ) ? VSP_URL . $extra : VSP_PATH . $extra;
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
		foreach ( vsp_get_file_paths( $search_type ) as $src ) {
			if ( $is_require && $once ) {
				require_once $src;
			} elseif ( $is_require ) {
				require $src;
			} elseif ( $once ) {
				include_once $src;
			} else {
				include $src;
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
	 */
	function vsp_get_file_paths( $path ) {
		return glob( $path );
	}
}

if ( ! function_exists( 'vsp_list_files' ) ) {
	/**
	 * Generates Nested Array Output of Files inside a folder.
	 *
	 * @param       $path
	 * @param int   $levels
	 * @param array $exclusions
	 *
	 * @return bool|string|array
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
			'plugin_name'     => false, // Your Plugin Name.
			'req_plugin'      => false, // Plugin File Eg : woocommerce/woocommerce.php
			'req_plugin_name' => false, // Name of the plugin.
			'version'         => false, // Plugin Version
			'compare'         => 'gte', // Eg : gte,gt.lt,lte
		) );

		if ( ! wp_is_plugin_active( $args['req_plugin'] ) ) {
			// translators: Add Requested Plugin Name & Required Plugin Name
			$msg = __( '%1$s Requires %2$s to be installed & activated.', 'vsp-framework' );
			$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong><i>' . $args['req_plugin_name'] . '</i></strong>' );
		}

		if ( false !== $args['version'] && false === $msg ) {
			switch ( $args['compare'] ) {
				case 'gte':
				case '>=':
					if ( ! plugin_version_gte( $args['req_plugin'], $args['version'] ) ) {
						// translators: Add Requested Plugin Name & Required Plugin Name & Required Plugin Version.
						$msg = __( '%1$s Requires %2$s Version %3$s Or Higher. Please Update Your %2$s To %3$s', 'vsp-framework' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;
				case 'gt':
				case '>':
					if ( ! plugin_version_gt( $args['req_plugin'], $args['version'] ) ) {
						// translators: Add Requested Plugin Name & Required Plugin Name & Required Plugin Version.
						$msg = __( '%1$s Requires %2$s Version %3$s. Please Update Your %2$s To %3$s', 'vsp-framework' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;
				case 'lt':
				case '<':
					if ( ! plugin_version_lt( $args['req_plugin'], $args['version'] ) ) {
						// translators: Add Requested Plugin Name & Required Plugin Name & Required Plugin Version.
						$msg = __( '%1$s Requires %2$s Version %3$s. Please Downgrade Your %2$s', 'vsp-framework' );
						$msg = sprintf( $msg, '<strong>' . $args['plugin_name'] . '</strong>', '<strong>' . $args['req_plugin_name'] . '</strong>', '<code>' . $args['version'] . '</code>' );
					}
					break;

				case 'lte':
				case '<=':
					if ( ! plugin_version_lte( $args['req_plugin'], $args['version'] ) ) {
						// translators: Add Requested Plugin Name & Required Plugin Name & Required Plugin Version.
						$msg = __( '%1$s Requires %2$s Version %3$s Or Lower. Please Downgrade Your %2$s To %3$s', 'vsp-framework' );
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

		return ( false !== $msg );
	}
}

if ( ! function_exists( 'vsp_add_wc_required_notice' ) ) {
	/**
	 * Adds WooCommerce Plugin Required Notice if not exists.
	 * Also checks for version based on plugin's input.
	 *
	 * @param string $name
	 * @param string $version
	 * @param string $compare
	 *
	 * @return bool
	 */
	function vsp_add_wc_required_notice( $name = '', $version = '3.0', $compare = '>=' ) {
		return vsp_validate_required_plugin( array(
			'plugin_name'     => $name,
			'req_plugin'      => 'woocommerce/woocommerce.php',
			'req_plugin_name' => 'WooCommerce',
			'version'         => $version,
			'compare'         => $compare,
		) );
	}
}

vsp_load_file( VSP_PATH . 'includes/functions/*.php' );
