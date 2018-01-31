<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 04-01-2018
 * Time: 03:42 PM
 */

interface VSP_Framework_Interface {

    /**
     * Admin Required Functions
     */
    public function admin_loaded();

    public function on_admin_init();

    public function admin_enqueue_assets();

    /**
     * Front End Required Functions
     */
    public function settings_init_before();

    public function init();

    public function init_hooks();

    public function addons_init();

    public function settings_init();

    public function add_assets();
}