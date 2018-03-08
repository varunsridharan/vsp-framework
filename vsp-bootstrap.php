<?php
/**
 * Plugin Name: VSP Framework
 * Framework Name: VSP Framework
 * Version: 080320180719 - Build 1
 * Author: Varun Sridharan
 * Author:            Varun Sridharan
 * Author URI:        http://varunsridharan.in
 * Text Domain:       vsp-framework
 * Domain Path: languages/
 */
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! function_exists("vsp_version") ) {
    /**
     * @return string
     */
    function vsp_version() {
        return '190220181146';
    }

    defined("VSP_VERSION") or define("VSP_VERSION", vsp_version());
    defined("VSP_PATH") or define("VSP_PATH", plugin_dir_path(__FILE__));
    defined("VSP_URL") or define("VSP_URL", trailingslashit(plugins_url("", __FILE__)));
    defined("VSP_CORE") or define("VSP_CORE", VSP_PATH . 'core/');

    require_once( VSP_CORE . 'class-autoloader.php' );
    require_once( VSP_CORE . 'class-cache.php' );
    require_once( VSP_PATH . 'vsp-functions.php' );
    require_once( VSP_PATH . 'functions/options.php' );
    require_once( VSP_PATH . 'functions/wp-replacement.php' );
    require_once( VSP_PATH . 'functions/general-functions.php' );
    require_once( VSP_PATH . 'functions/admin-notices-functions.php' );
    require_once( VSP_PATH . 'vsp-hooks.php' );

    do_action('vsp_framework_load_lib_integrations');

    do_action("vsp_framework_loaded");

    if( vsp_is_ajax() ) {
        require_once( VSP_CORE . 'class-core-ajax.php' );
    }

    /**
     * Framework load text domain
     * @since 1.0.0
     * @version 1.0.0
     */
    load_textdomain('vsp-framework', VSP_PATH . '/languages/' . get_locale() . '.mo');

    do_action("vsp_framework_init");
}
