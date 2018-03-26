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

		if ( file_exists( VSP_CORE . 'abstract/' . $file_name ) ) {
			require_once VSP_CORE . 'abstract/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'trait/' . $file_name ) ) {
			require_once VSP_CORE . 'trait/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'interface/' . $file_name ) ) {
			require_once VSP_CORE . 'interface/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'helpers/' . $file_name ) ) {
			require_once VSP_CORE . 'helpers/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'helpers/woocommerce/' . $file_name ) ) {
			require_once VSP_CORE . 'helpers/woocommerce/' . $file_name;
		} elseif ( file_exists( VSP_CORE . '' . $file_name ) ) {
			require_once VSP_CORE . '' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'modules/addons/' . $file_name ) ) {
			require_once VSP_CORE . 'modules/addons/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'modules/admin-notices/' . $file_name ) ) {
			require_once VSP_CORE . 'modules/admin-notices/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'modules/settings/' . $file_name ) ) {
			require_once VSP_CORE . 'modules/settings/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'modules/wp-importer/' . $file_name ) ) {
			require_once VSP_CORE . 'modules/wp-importer/' . $file_name;
		} elseif ( file_exists( VSP_CORE . 'modules/' . $file_name ) ) {
			require_once VSP_CORE . 'modules/' . $file_name;
		} elseif ( has_action( 'vsp_load_' . $classname ) ) {
			do_action( 'vsp_load_' . $classname, $classname, $file_name );
		}
	}

	/**
	 * Loads An Integration
	 *
	 * @param string $integration .
	 *
	 * @return bool
	 */
	public static function integration( $integration = '' ) {
		$integration = strtolower( $integration );
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
		}
		return false;
	}

	/**
	 * Returns Integration Path
	 *
	 * @return string
	 */
	public static function integration_path() {
		return VSP_PATH . 'integrations/';
	}

	/**
	 * Loads A lib
	 *
	 * @param string $lib .
	 *
	 * @return bool
	 */
	public static function library( $lib = '' ) {
		$lib = strtolower( $lib );
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
		}
		return false;
	}

	/**
	 * Returns Lib path
	 *
	 * @return string
	 */
	public static function lib_path() {
		return VSP_PATH . 'libs/';
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
