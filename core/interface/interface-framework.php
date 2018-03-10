<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 04-01-2018
 * Time: 03:42 PM
 */

interface VSP_Framework_Interface {
    /* @see VSP_Framework::__init_plugin() */
    public function plugin_init();

    /* @see   VSP_Framework::__register_hooks */
    public function register_hooks();

    /* @see VSP_Framework::__settings_init */
    public function settings_init_before();

    /* @see \VSP_Framework::__load_required_files */
    public function load_files();
}