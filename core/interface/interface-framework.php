<?php
/**
 * Interface class for plugins that uses VSP-Framework
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 04-01-2018
 * Time: 03:42 PM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/interface
 * @copyright GPL V3 Or greater
 */

/**
 * Interface VSP_Framework_Interface
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
interface VSP_Framework_Interface {
	/**
	 * @see VSP_Framework::__init_plugin() .
	 */
	public function plugin_init();

	/**
	 * @see   VSP_Framework::__register_hooks
	 */
	public function register_hooks();

	/**
	 * @see VSP_Framework::__settings_init
	 */
	public function settings_init_before();

	/**
	 * @see \VSP_Framework::__load_required_files
	 */
	public function load_files();
}
