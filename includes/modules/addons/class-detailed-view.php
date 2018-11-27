<?php
/**
 * VSP Plugin Addon Detailed Class.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/modules/addons
 * @copyright GPL V3 Or greater
 */

namespace VSP\Modules\Addons;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Addons_Detailed_View
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
abstract class Detailed_View extends File_Meta {


	/**
	 * VSP_Addons_Detailed_View constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_filter( 'plugins_api', array( $this, 'enable_addon_viewdetails' ), 10, 100 );
	}

	/**
	 * Renders Addons Full Detail View
	 *
	 * @param object $result .
	 * @param string $action .
	 * @param object $args .
	 *
	 * @return array|bool|mixed|object|\WP_Error
	 */
	public function enable_addon_viewdetails( $result, $action, $args ) {
		if ( ! isset( $_REQUEST['isvspaddon'] ) ) {
			return $result;
		}

		$addon_folder = trim( dirname( $args->slug ), '/' );
		$result       = vsp_get_cdn( $this->slug() . '/addons/' . $addon_folder . '.json' );

		if ( false !== $result ) {
			if ( is_object( $result ) || is_array( $result ) ) {
				$result->banners  = (array) $result->banners;
				$result->sections = (array) $result->sections;
			} else {
				$result = false;
			}
		}

		return $result;
	}
}
