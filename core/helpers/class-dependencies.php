<?php
/**
 * WP Dependencies Checker.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 14-02-2018
 * Time: 03:57 PM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/helpers
 * @copyright GPL V3 Or greater
 */

/**
 * Class VSP_Dependencies
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class VSP_Dependencies {

	/**
	 * Array of active_plugins
	 *
	 * @var $active_plugins
	 */
	private static $active_plugins;

	/**
	 * Checks if given plugin is active
	 *
	 * @param string $file Plugin File Name .
	 *
	 * @return bool
	 */
	public static function active_check( $file ) {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		return in_array( $file, self::$active_plugins, true ) || array_key_exists( $file, self::$active_plugins );
	}

	/**
	 * Inits VSP_Dependencies class
	 */
	public static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}
}
