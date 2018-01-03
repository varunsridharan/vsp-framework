<?php
/** 
 * Plugin Name: VSP Framework
 * Framework Name: VSP Framework
 * Version: 1.1
 * Author: Varun Sridharan
 * Author:            Varun Sridharan
 * Author URI:        http://varunsridharan.in
 * Text Domain:       vsp-framework
 * Domain Path: languages/
 */
if(!defined("ABSPATH")){ exit; }

if(!function_exists("vsp_version")){
    function vsp_version(){ return '1.1'; }    

    defined("VSP_V") or define("VSP_V",vsp_version());
    defined("VSP_PATH") or define("VSP_PATH",plugin_dir_path(__FILE__));
    defined("VSP_URL") or define("VSP_URL",trailingslashit(plugins_url("",__FILE__)));

    require_once(plugin_dir_path(__FILE__).'vsp-functions.php');
    require_once(VSP_PATH.'functions/settings-functions.php');
    require_once(VSP_PATH.'functions/wp-replacement.php');
    require_once(VSP_PATH.'functions/general-functions.php');
    require_once(VSP_PATH.'functions/cache-variables.php');
    require_once(VSP_PATH.'functions/admin-notices-functions.php');
    require_once(VSP_PATH.'libs/wpsf/wpsf-framework.php');
    require_once(VSP_PATH.'vsp-hooks.php');

    do_action("vsp_framework_loaded");

    if(vsp_is_request("admin") || vsp_is_request("ajax")){
        require_once(VSP_PATH.'class/class-vsp-taxonomy-handler.php');
    }

    if(vsp_is_request('ajax')){
        require_once(VSP_PATH.'class/class-vsp-framework-core-ajax.php');
    }
    
    /**
     * Framework load text domain
     * @since 1.0.0
     * @version 1.0.0
     */
    load_textdomain ( 'vsp-framework', VSP_PATH . '/languages/' . get_locale () . '.mo' );
    
    do_action("vsp_framework_init");
}
