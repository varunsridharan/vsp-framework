<?php
/** 
 * Framework Name: VSP Framework
 * Version: 1.0
 * Author: Varun Sridharan
 */
if(!defined("ABSPATH")){ exit; }

if(!function_exists("vsp_version")){
    function vsp_version(){ return '1.1'; }    

    defined("VSP_V") or define("VSP_V",vsp_version());
    defined("VSP_PATH") or define("VSP_PATH",plugin_dir_path(__FILE__));
    defined("VSP_URL") or define("VSP_URL",trailingslashit(plugins_url("",__FILE__)));

    defined("VSP_JS_URL") or define("VSP_JS_URL",VSP_URL.'assets/js/');
    defined("VSP_CSS_URL") or define("VSP_CSS_URL",VSP_URL.'assets/css/');
    defined("VSP_IMG_URL") or define("VSP_IMG_URL",VSP_URL.'assets/img/');
    
    require_once(plugin_dir_path(__FILE__).'vsp-functions.php');
    require_once(plugin_dir_path(__FILE__).'vsp-constants.php');
    require_once(VSP_PATH.'functions/settings-functions.php');
    require_once(VSP_PATH.'functions/wp-replacement.php');
    require_once(VSP_PATH.'functions/general-functions.php');
    require_once(VSP_PATH.'functions/cache-variables.php');
    require_once(VSP_PATH.'functions/admin-notices-functions.php');
    require_once(VSP_PATH.'libs/wpsf/wpsf-framework.php');
    require_once(VSP_PATH.'vsp-hooks.php');
    
    do_action("vsp_framework_loaded");

    if(vsp_is_request('ajax')){
        new VSP_Framework_Core_Ajax;
    }
    
    do_action("vsp_framework_init");
}