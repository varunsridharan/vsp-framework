<?php
/** 
 * Framework Name: VSP Framework
 * Version: 1.4
 * Author: Varun Sridharan
 */
if(!defined("ABSPATH")){ exit; }

if(!function_exists("vsp_version")){
    function vsp_version(){ return '1.4'; }    
    
    require_once(plugin_dir_path(__FILE__).'vsp-functions.php');
    
    require_once(plugin_dir_path(__FILE__).'vsp-constants.php');
    
    require_once(VSP_FUNCTIONS.'general-functions.php');
    
    require_once(VSP_FUNCTIONS.'file-handler.php');
    
    require_once(VSP_FUNCTIONS.'cache-variables.php');
    
    require_once(VSP_FUNCTIONS.'admin-notices-functions.php');
    
    require_once(VSP_PATH.'vsp-hooks.php');
    
    require_once(VSP_CLASS.'class-framework-class-handler.php');
    
    require_once(VSP_CLASS.'tools/class-admin-notices.php');


    if(vsp_is_request('ajax')){
        require_once(VSP_CLASS.'class-framework-core-ajax.php');
    }
    
    if(vsp_is_request("admin")){
        require_once(VSP_CLASS.'tools/class-site-status-report.php');    
    
        require_once(VSP_CLASS.'tools/class-settings-status-page.php');
    }

    
    require_once(VSP_CLASS."addons/class-addons-filemeta.php");
    
    require_once(VSP_CLASS."addons/class-addons-core.php");
    
    require_once(VSP_CLASS."addons/class-addons-admin.php");
    
    require_once(VSP_CLASS."addons/class-addons.php");
    
    require_once(VSP_CLASS."settings/class-settings-fields.php");
    
    require_once(VSP_CLASS."settings/class-settings-handler.php");
    
    require_once(VSP_CLASS."settings/class-settings-init.php");
    
    require_once(VSP_CLASS.'class-framework-hooks.php');
    
    require_once(VSP_CLASS.'class-framework-options.php');
    
    require_once(VSP_CLASS.'class-framework-init.php');
    
    require_once(VSP_CLASS.'class-framework.php');
    
    do_action("vsp_framework_loaded");
}