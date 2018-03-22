<?php

/**
 * Created by PhpStorm.
 * User: varun
 * Date: 28-02-2018
 * Time: 01:05 PM
 *
 * Trait VSP_Framework_Trait
 *
 * @package vsp-framework
 */
trait VSP_Framework_Trait {
	/**
	 * VSP_Framework::__init_plugin()
	 *
	 * @see VSP_Framework::__init_plugin()
	 */
	public function plugin_init_before() {
	}

	/**
	 * VSP_Framework::__init_class()
	 *
	 * @see VSP_Framework::__init_class()
	 */
	public function init_class() {
	}

	/**
	 * VSP_Framework::__addon_init
	 *
	 * @see VSP_Framework::__addon_init
	 */
	public function addon_init_before() {

	}

	/**
	 * VSP_Framework::__addon_init
	 *
	 * @see VSP_Framework::__addon_init
	 */
	public function addon_init() {

	}

	/**
	 * VSP_Framework::__settings_init
	 *
	 * @see VSP_Framework::__settings_init
	 */
	public function settings_init() {

	}

	/**
	 * \VSP_Framework::__wp_init
	 *
	 * @see \VSP_Framework::__wp_init
	 */
	public function wp_init() {

	}

	/**
	 * \VSP_Framework::__admin_init
	 *
	 * @see \VSP_Framework::__admin_init
	 */
	public function admin_init() {

	}

	/**
	 * \VSP_Framework::__admin_assets()
	 *
	 * @see \VSP_Framework::__admin_assets()
	 */
	public function admin_assets() {
	}

	/**
	 * \VSP_Framework_Admin::__register_admin_hooks
	 *
	 * @see \VSP_Framework_Admin::__register_admin_hooks
	 */
	public function wp_admin_init() {
	}

	/**
	 * \VSP_Framework::__register_hooks()
	 *
	 * @see \VSP_Framework::__register_hooks()
	 */
	public function load_textdomain() {

	}

	/**
	 * \VSP_Framework::register_hooks()
	 *
	 * @see \VSP_Framework::register_hooks()
	 */
	public function frontend_assets() {
	}
}