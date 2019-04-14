<?php
/**
 * Interface class for plugins that uses VSP-Framework/wpsf
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:09 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/interface
 * @copyright GPL V3 Or greater
 */

namespace VSP\Core\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Interface VSP_Plugin_Settings_Interface
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
interface Plugin_Settings {
	/**
	 * Settings Args
	 *
	 * @param array|\WPO\Builder $builder .
	 *
	 * @return mixed
	 */
	public function options( $builder );
}
