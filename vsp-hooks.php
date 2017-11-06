<?php
if(!defined("ABSPATH")){ exit; }

add_action("admin_enqueue_scripts",'vsp_register_assets',1);
add_action("vsp_framework_init",'vsp_init_admin_notices');

if(!function_exists("vsp_register_assets")){
    /**
     * Registers Basic Framework Styles / Scripts to WP
     */
    function vsp_register_assets(){
        wp_register_script('vsp-select2',vsp_js('select2.full.min.js'),array('jquery'),'2 4.0.4',true);
        wp_register_script('vsp-plugins',vsp_js('vsp-plugins.min.js'),array('jquery'),'1.0',true);
        wp_register_script('vsp-addons',vsp_js('vsp-addons.js'),array('jquery'),'1.0',true);
        wp_register_script('vsp-framework',vsp_js('vsp-framework.js'),array('vsp-plugins'),'1.0',true);
        
        wp_register_style('vsp-plugins-css',vsp_css('vsp-plugins.min.css'));
        wp_register_style('vsp-framework-css',vsp_css('vsp-framework.min.css'));
        wp_register_style('vsp-select2',vsp_css('select2.min.css'),'','2 4.0.4',false);
    }
}

if(!function_exists("vsp_init_admin_notices")){
    function vsp_init_admin_notices(){
        if(vsp_is_request("admin") || vsp_is_request("ajax")){
            vsp_notices();
        }
    }
}