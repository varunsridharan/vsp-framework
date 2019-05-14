<?php
/**
 * VSP Framework Trait
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 28-02-2018
 * Time: 01:05 PM
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
 * Trait VSP_Framework_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait Framework {
	/**
	 * @see VSP_Framework::_init_plugin()
	 */
	public function plugin_init_before() {
	}

	/**
	 * @see VSP_Framework::_init_class()
	 */
	public function init_class() {
	}

	/**
	 * @see VSP_Framework::_settings_init
	 */
	public function settings_init() {
	}

	/**
	 * @see VSP_Framework::_settings_init
	 */
	public function settings_init_before() {

	}

	/**
	 * @see \VSP_Framework::_wp_init
	 */
	public function wp_init() {
	}

	/**
	 * @see \VSP_Framework::_admin_init
	 */
	public function admin_init() {
	}

	/**
	 * @see \VSP_Framework::_admin_assets()
	 */
	public function admin_assets() {
	}

	/**
	 * @see \VSP_Framework_Admin::_register_admin_hooks
	 */
	public function wp_admin_init() {
	}

	/**
	 * @see \VSP_Framework::register_hooks()
	 */
	public function frontend_assets() {
	}

	/**
	 * @see VSP_Framework::_init_plugin() .
	 */
	public function plugin_init() {
	}
}
