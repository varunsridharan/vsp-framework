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
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VSP_Addons_Detailed_View
 */
class VSP_Addons_Detailed_View extends VSP_Addons_FileMeta {
	public function __construct() {
		parent::__construct();
		add_filter( 'plugins_api', array( $this, 'enable_addon_viewdetails' ), 10, 100 );
	}

	/**
	 * Renders Addons Full Detail View
	 *
	 * @param object $result .
	 * @param string $action .
	 * @param array  $args .
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