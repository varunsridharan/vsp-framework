<?php
/**
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 * @link
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! function_exists( 'wp_is_plugin_active' ) ) {
	/**
	 * Checks if Plugin is active.
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::is_active()
	 *
	 */
	function wp_is_plugin_active( $plugin_file ) {
		return \Varunsridharan\WordPress\Dependencies::is_active( $plugin_file );
	}
}

if ( ! function_exists( 'wp_is_plugin_installed' ) ) {
	/**
	 * Checks if given plugin is installed or not.
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::is_active()
	 */
	function wp_is_plugin_installed( $plugin_file ) {
		return \Varunsridharan\WordPress\Dependencies::is_installed( $plugin_file );
	}
}

if ( ! function_exists( 'wp_is_plugin_network_active' ) ) {
	/**
	 * Checks if Plugin is Network Active.
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::is_network_active()
	 *
	 */
	function wp_is_plugin_network_active( $plugin_file ) {
		return \Varunsridharan\WordPress\Dependencies::is_network_active( $plugin_file );
	}
}

if ( ! function_exists( 'wp_is_site_plugin_active' ) ) {
	/**
	 * Checks if Plugin is Active Just for the current site. (Used only if its network enable WP install)
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::is_site_active()
	 *
	 */
	function wp_is_site_plugin_active( $plugin_file ) {
		return \Varunsridharan\WordPress\Dependencies::is_site_active( $plugin_file );
	}
}

if ( ! function_exists( 'wp_is_plugin_inactive' ) ) {
	/**
	 * Checks if Plugin Is InActive.
	 *
	 * @param $plugin_file
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::is_inactive()
	 *
	 */
	function wp_is_plugin_inactive( $plugin_file ) {
		return \Varunsridharan\WordPress\Dependencies::is_inactive( $plugin_file );
	}
}

if ( ! function_exists( 'plugin_version' ) ) {
	/**
	 * Returns Version for the given plugin slug.
	 *
	 * @param $plugin
	 *
	 * @return string|bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version()
	 *
	 */
	function plugin_version( $plugin ) {
		return \Varunsridharan\WordPress\Dependencies::version( $plugin );
	}
}

if ( ! function_exists( 'plugin_version_gt' ) ) {
	/**
	 * Validates if plugin's version is greater to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_gt()
	 *
	 */
	function plugin_version_gt( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_gt( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'plugin_version_gte' ) ) {
	/**
	 * Validates if plugin's version is greater or equal to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_gte()
	 *
	 */
	function plugin_version_gte( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_gte( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'plugin_version_lte' ) ) {
	/**
	 * Validates if plugin's version is less or equal to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_lte()
	 *
	 */
	function plugin_version_lte( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_lte( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'plugin_version_lt' ) ) {
	/**
	 * Validates if plugin's version is less or equal to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_lt()
	 *
	 */
	function plugin_version_lt( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_lt( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'is_version_gt' ) ) {
	/**
	 * Validates if plugin's version is greater to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_gt()
	 *
	 */
	function is_version_gt( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_gt( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'is_version_gte' ) ) {
	/**
	 * Validates if plugin's version is greater or equal to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_gte()
	 *
	 */
	function is_version_gte( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_gte( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'is_version_lte' ) ) {
	/**
	 * Validates if plugin's version is less or equal to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_lte()
	 *
	 */
	function is_version_lte( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_lte( $plugin, $compare_version );
	}
}

if ( ! function_exists( 'is_version_lt' ) ) {
	/**
	 * Validates if plugin's version is less or equal to the version you provided.
	 *
	 * @param $plugin
	 * @param $compare_version
	 *
	 * @return bool
	 * @uses \Varunsridharan\WordPress\Dependencies::version_lt()
	 *
	 */
	function is_version_lt( $plugin, $compare_version ) {
		return \Varunsridharan\WordPress\Dependencies::version_lt( $plugin, $compare_version );
	}
}
