<?php
/**
 * VSP File Trait
 *
 * Created by PhpStorm.
 * User: varun
 * Date : 13-10-2018
 * Time : 01:42 PM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/trait
 * @copyright GPL V3 Or greater
 */

namespace VSP\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * Trait VSP_File_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait File {
	/**
	 * Returns File Contents.
	 *
	 * @param $file
	 *
	 * @static
	 * @return bool|false|string
	 */
	public static function get_contents( $file ) {
		try {
			return @file_get_contents( $file );
		} catch ( \ErrorException $exception ) {

		}
		return false;
	}
}
