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
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

/**
 * Class VSP_Autoloader
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
final class VSP_Autoloader {
	/**
	 * List of Integration Bundled With VSP
	 *
	 * @var array
	 */
	private static $_integrations = array(
		'wpsf'            => 'wpsf.php',
		'visual-composer' => 'visual-composer.php',
	);

	/**
	 * List of libs bundled with VSP
	 *
	 * @var array
	 */
	private static $_libs = array(
		'wp-async'     => 'async.php',
		'vs-transient' => 'vs-transient.php',
		'wpsf'         => 'wpsf.php',
		'wpreview'     => 'review-me.php',
		'wpallimport'  => 'wpallimport.php',
		'wppointer'    => 'wp-pointers/wp-pointers.php',
		'wp-endpoints' => 'vs-wp-endpoint.php',
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

	private static $core_folders = null;

	/**
	 * Loads A Class if its from VSP
	 *
	 * @param string $class Class Name.
	 */
	public static function load( $class = '' ) {
		if ( strpos( $class, 'VSP_' ) !== false ) {
			$filename = str_replace( '_', '-', strtolower( $class ) );

			if ( 'vsp-' === substr( $filename, 0, strlen( 'vsp-' ) ) ) {
				$filename = substr( $filename, strlen( 'vsp_' ) );
			}

			$prefix = 'class-';
			$surfix = '.php';

			if ( strpos( $filename, 'class' ) !== false ) {
				$prefix = '';
			} elseif ( strpos( $filename, 'interface' ) !== false ) {
				$filename = str_replace( 'interface', '', $filename );
				$prefix   = 'interface-';
			} elseif ( strpos( $filename, 'trait' ) !== false ) {
				$filename = str_replace( 'trait', '', $filename );
				$prefix   = 'trait-';
			}

			$filename = $prefix . self::fix_filename( $filename ) . $surfix;
			self::check_load( $class, $filename );
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
		$folders = self::get_folders( VSP_PATH . 'core' );

		foreach ( $folders as $folder ) {
			if ( file_exists( $folder . $file_name ) ) {
				require_once $folder . $file_name;
				break;
			} elseif ( has_action( 'vsp_load_' . $classname ) ) {
				do_action( 'vsp_load_' . $classname, $classname, $file_name );
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
	 * Loads An Integration
	 *
	 * @param string $integration .
	 *
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
				sprintf( __( 'Failed To Load Requested Integration %s' ), $_integration ),
				sprintf( __( 'Searched In Path %s', 'vsp-framework' ), self::integration_path( true ) ),
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
	 * Loads A lib
	 *
	 * @param string $_lib .
	 *
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
				sprintf( __( 'Failed To Load Requested Library %s' ), $_lib ),
				sprintf( __( 'Searched In Path %s', 'vsp-framework' ), self::lib_path( true ) ),
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
		$path = VSP_PATH . 'libs/';
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
