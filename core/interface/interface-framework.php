<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 04-01-2018
 * Time: 03:42 PM
 */

interface VSP_Framework_Interface {

    /**
     * This function is called init hook
     * @hook init
     * @return mixed
     */
    public function wp_init();

    /**
     * This function is called on vsp_framework_init
     * @hook vsp_framework_init
     * @return mixed
     */
    public function init();

    public function register_hooks();

    public function settings_init_before();

    public function wp_admin_init();

    public function on_admin_init();

}