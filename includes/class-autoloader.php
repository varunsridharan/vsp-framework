<?php
/**
 * VSP Framework Autoloader.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 08:39 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core
 * @copyright GPL V3 Or greater
 */

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Autoloader
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
final class Autoloader {
	/**
	 * List of Integration Bundled With VSP
	 *
	 * @var array
	 */
	private static $_integrations = array(
		'visual-composer' => 'visual-composer.php',
	);

	/**
	 * List of libs bundled with VSP
	 *
	 * @var array
	 */
	private static $_libs = array(
		'wp-async'      => 'a5hleyrich/wp-background-processing/wp-background-processing.php',
		'wp-pointer'    => 'wpbp/pointerplus/pointerplus.php',
		'wp-ajaxer'     => 'varunsridharan/wp-ajaxer/class-ajaxer.php',
		'wp-endpoint'   => 'varunsridharan/wp-endpoint/class-endpoint.php',
		'wp-post'       => 'varunsridharan/wp-post/class-post.php',
		'wp-review-me'  => 'varunsridharan/wp-review-me/class-wp-review-me.php',
		'wp-transient'  => 'varunsridharan/wp-transient-api/class-transient-wp-api.php',
		'wp-db-table'   => 'varunsridharan/wp-db-table/class-db-table.php',
		'wp-all-import' => 'soflyy/wp-all-import-rapid-addon/rapid-addon.php',
	);

	/**
	 * Loaded_libs
	 *
	 * @var array
	 */
	private static $_loaded_libs = array();

	/**
	 * Loaded_integrations
	 *
	 * @var array
	 */
	private static $_loaded_integrations = array();

	/**
	 * @param string $_integration
	 *
	 * @static
	 * @return bool
	 */
	public static function integration( $_integration = '' ) {
		$integration = strtolower( $_integration );
		if ( isset( self::$_integrations[ $integration ] ) && ! isset( self::$_loaded_integrations[ $integration ] ) ) {
			if ( file_exists( self::integration_path() . self::$_integrations[ $integration ] ) ) {
				require_once self::integration_path() . self::$_integrations[ $integration ];
				self::$_loaded_integrations[ $integration ] = $integration;
				return true;
			} elseif ( has_action( 'vsp_integration_' . $integration ) ) {
				do_action( 'vsp_integration_' . $integration );
				self::$_loaded_integrations[ $integration ] = $integration;
				return true;
			}
		} elseif ( ! isset( self::$_loaded_integrations[ $integration ] ) ) {
			vsp_log_msg( array(
				/* translators: Adds integration Name */
				sprintf( __( 'Failed To Load Requested Integration %s' ), $_integration ),
				/* translators: Adds integration Name */
				sprintf( __( 'Searched In Path %s', 'vsp-framework' ), self::integration_path( true ) ),
				/* translators: Adds integration Action Name */
				sprintf( __( 'Tried Action %s', 'vsp-framework' ), 'vsp_lib_' . $integration ),
			), 'critical' );
		}
		return false;
	}

	/**
	 * Returns Integration Path
	 *
	 * @param bool $is_censored_path
	 *
	 * @return string
	 * @static
	 */
	public static function integration_path( $is_censored_path = false ) {
		$path = VSP_PATH . 'integrations/';
		if ( true === $is_censored_path ) {
			$path = basename( ABSPATH ) . str_replace( vsp_unslashit( ABSPATH ), '', $path );
		}
		return $path;
	}

	/**
	 * @param string $_lib
	 *
	 * @static
	 * @return bool
	 */
	public static function library( $_lib = '' ) {
		$lib = strtolower( $_lib );

		if ( isset( self::$_libs[ $lib ] ) && ! isset( self::$_loaded_libs[ $lib ] ) ) {
			if ( file_exists( self::lib_path() . self::$_libs[ $lib ] ) ) {
				require_once self::lib_path() . self::$_libs[ $lib ];
				self::$_loaded_libs[ $lib ] = $lib;
				return true;
			} elseif ( has_action( 'vsp_lib_' . $lib ) ) {
				do_action( 'vsp_lib_' . $lib );
				self::$_loaded_libs[ $lib ] = $lib;
				return true;
			}
		} elseif ( ! isset( self::$_loaded_libs[ $lib ] ) ) {
			vsp_log_msg( array(
				/* translators: Adds Library Name */
				sprintf( __( 'Failed To Load Requested Library %s' ), $_lib ),
				/* translators: Adds Library Search Path */
				sprintf( __( 'Searched In Path %s', 'vsp-framework' ), self::lib_path( true ) ),
				/* translators: Adds Library Triggered Action */
				sprintf( __( 'Tried Action %s', 'vsp-framework' ), 'vsp_lib_' . $lib ),
			), 'critical' );
		}

		return false;
	}

	/**
	 * Returns Lib path
	 *
	 * @param bool $is_censored_path
	 *
	 * @return string
	 * @static
	 */
	public static function lib_path( $is_censored_path = false ) {
		$path = VSP_PATH . 'vendor/';
		if ( true === $is_censored_path ) {
			$path = basename( ABSPATH ) . str_replace( vsp_unslashit( ABSPATH ), '', $path );
		}
		return $path;
	}

	/**
	 * Returns list of all libs bundled with VSP
	 *
	 * @return array
	 */
	public static function get_libs() {
		return self::$_libs;
	}

	/**
	 * Returns list of all integration bundled with VSP
	 *
	 * @return array
	 */
	public static function get_integrations() {
		return self::$_integrations;
	}
}
