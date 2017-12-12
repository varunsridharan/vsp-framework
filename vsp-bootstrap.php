<?php
/** 
 * Framework Name: VSP Framework
 * Version: 1.0
 * Author: Varun Sridharan
 */
if(!defined("ABSPATH")){ exit; }

if(!function_exists("vsp_version")){
    function vsp_version(){ return '1.0'; }    
    
    require_once(plugin_dir_path(__FILE__).'vsp-functions.php');
    require_once(plugin_dir_path(__FILE__).'vsp-constants.php');
    require_once(VSP_FUNCTIONS.'settings-functions.php');
    require_once(VSP_FUNCTIONS.'wp-replacement.php');
    require_once(VSP_FUNCTIONS.'general-functions.php');
    require_once(VSP_FUNCTIONS.'cache-variables.php');
    require_once(VSP_FUNCTIONS.'admin-notices-functions.php');
    
    
    require_once(VSP_PATH.'vsp-hooks.php');
    do_action("vsp_framework_loaded");

    if(vsp_is_request('ajax')){
        new VSP_Framework_Core_Ajax;
    }
    
    do_action("vsp_framework_init");
}