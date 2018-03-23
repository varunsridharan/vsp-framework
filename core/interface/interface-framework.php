<?php

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 04-01-2018
 * Time: 03:42 PM
 *
 * @package vsp-framework
 *
 * Interface VSP_Framework_Interface
 */
interface VSP_Framework_Interface {
	/**
	 * VSP_Framework::__init_plugin()
	 *
	 * @see VSP_Framework::__init_plugin() .
	 */
	public function plugin_init();

	/**
	 * VSP_Framework::__register_hooks
	 *
	 * @see   VSP_Framework::__register_hooks
	 */
	public function register_hooks();

	/**
	 * VSP_Framework::__settings_init
	 *
	 * @see VSP_Framework::__settings_init
	 */
	public function settings_init_before();

	/**
	 * \VSP_Framework::__load_required_files
	 *
	 * @see \VSP_Framework::__load_required_files
	 */
	public function load_files();
}