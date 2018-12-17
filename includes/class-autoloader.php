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
	 * core_folders
	 *
	 * @var null
	 */
	private static $core_folders = null;

	/**
	 * Loads A Class if its from VSP
	 *
	 * @param string $req_class
	 *
	 * @static
	 */
	public static function load( $req_class = '' ) {
		if ( strpos( $req_class, 'VSP\\' ) !== false ) {
			$file   = explode( '\\', $req_class );
			$file   = end( $file );
			$file   = str_replace( '_', '-', strtolower( $file ) );
			$prefix = 'class-';
			$surfix = '';

			if ( strpos( $req_class, '\\Traits\\' ) !== false ) {

			} elseif ( strpos( $req_class, '\\Interfaces\\' ) !== false ) {
				$prefix = '';
			}

			self::check_load( $req_class, $prefix . $file . $surfix . '.php' );
		}
	}

	/**
	 * Fixes File name by removing -,_ and space in the file name string
	 *
	 * @param string $file_name .
	 *
	 * @return string
	 */
	public static function fix_filename( $file_name = '' ) {
		$file_name = trim( $file_name, '-' );
		$file_name = trim( $file_name, '_' );
		$file_name = trim( $file_name, ' ' );
		return $file_name;
	}

	/**
	 * Validates Path And Loads the class file.
	 *
	 * @param string $classname
	 * @param string $file_name
	 */
	public static function check_load( $classname = '', $file_name = '' ) {
		$class = str_replace( 'VSP\\', '', $classname );
		$file  = explode( '\\', $classname );
		$file  = end( $file );
		$class = str_replace( $file, '', $class );
		$class = strtolower( str_replace( '\\', '/', $class ) );

		if ( file_exists( VSP_PATH . 'includes/' . $class . $file_name ) ) {
			require_once VSP_PATH . 'includes/' . $class . $file_name;
		} else {
			$loaded  = false;
			$folders = self::get_folders( VSP_PATH . 'includes' );
			foreach ( $folders as $folder ) {
				if ( file_exists( $folder . $file_name ) ) {
					$loaded = true;
					require_once $folder . $file_name;
					break;
				} elseif ( has_action( 'vsp_load_' . $classname ) ) {
					$loaded = true;
					do_action( 'vsp_load_' . $classname, $classname, $file_name );
				}
			}
			if ( false === $loaded ) {
				var_dump( $classname, $file_name );
			}
		}
	}

	/**
	 * Saves & Returns Core Folders.
	 *
	 * @param string $path
	 *
	 * @return array|null|string
	 * @static
	 */
	public static function get_folders( $path = '' ) {
		if ( null === self::$core_folders ) {
			self::$core_folders = self::search_folders( $path );
		}
		return self::$core_folders;
	}

	/**
	 * Search folders & subfolders inside a given folder and returns it.
	 *
	 * @param string $path
	 *
	 * @return array|string
	 * @static
	 */
	public static function search_folders( $path = '' ) {
		$return       = array();
		$base_folders = array_filter( vsp_get_file_paths( vsp_unslashit( $path ) . '/*' ), 'is_dir' );

		if ( is_array( $base_folders ) && ! empty( $base_folders ) ) {
			foreach ( $base_folders as $folder ) {
				$_sub = self::search_folders( vsp_unslashit( $folder ) );

				if ( is_array( $_sub ) && ! empty( $_sub ) ) {
					$return = array_merge( $return, $_sub );
				} elseif ( is_string( $_sub ) ) {
					$return = array_merge( $return, array( $_sub ) );
					$return = array_merge( $return, array( vsp_slashit( $path ) ) );
				}
			}
		} else {
			$return = vsp_slashit( $path );
		}

		return $return;
	}

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
