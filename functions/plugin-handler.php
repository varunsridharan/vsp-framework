<?php
if(!defined("ABSPATH")){ exit; }

if(!function_exists("vsp_plugin_activator")){
    function vsp_plugin_activator($options = array()){
        if(! class_exists("VSP_Activator")){
            $path = defined('VSP_PATH') ? VSP_PATH : __DIR__.'/../';
            require_once($path.'helper/class-vsp-activator.php');
        }
                
        VSP_Activator::activate($options);
    }
}

if(!function_exists("vsp_plugin_deactivator")){
    function vsp_plugin_deactivator(){
        if(!class_exists("VSP_Deactivator")){
            $path = defined('VSP_PATH') ? VSP_PATH : __DIR__.'/../';
            require_once($path.'helper/class-vsp-deactivator.php');
        }
        
        VSP_Deactivator::deactivate();
    }
}

if(!function_exists("vsp_plugin_dependency_deactivator")){
    function vsp_plugin_dependency_deactivator($file){
        if(!class_exists("VSP_Deactivator")){
            $path = defined('VSP_PATH') ? VSP_PATH : __DIR__.'/../';
            require_once($path.'helper/class-vsp-deactivator.php');
        }
        VSP_Deactivator::dependency_deactivate($file);
    }
}