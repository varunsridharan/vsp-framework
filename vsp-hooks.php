<?php
if(!defined("ABSPATH")){ exit; }

add_action("vsp_framework_init",'vsp_init_admin_notices');
add_action("vsp_framework_loaded",'vsp_cache_options',99999);

if(vsp_is_request("admin")){
    add_action("admin_enqueue_scripts",'vsp_register_assets',1);
}


if(!function_exists("vsp_register_assets")){
    /**
     * Registers Basic Framework Styles / Scripts to WP
     */
    function vsp_register_assets(){
        
        $js = array(
            'bs-transition' => vsp_debug_file("libs/wpsf/assets/vendors/bootstrap/bootstrap-transition.js",'url'),
            'bs-popover' => vsp_debug_file("libs/wpsf/assets/vendors/bootstrap/popover/popover.js",'url'),
            'bs-tooltip' => vsp_debug_file("libs/wpsf/assets/vendors/bootstrap/tooltip/tooltip.js",'url'),
            'icheck' => vsp_debug_file("libs/wpsf/assets/vendors/icheck/jquery.icheck.js",'url'),
            'select2' => vsp_debug_file("libs/wpsf/assets/vendors/select2/select2.full.js",'url'),
            'blockui' => vsp_debug_file("vendors/blockui/jquery.blockui.js",'assets'),    
            'owlslider' => vsp_debug_file("vendors/owlslider/jquery.owl.js",'assets'),
            'simscroll' => vsp_debug_file('vendors/simscroll/simscroll.js','assets'),
            'vspajax' => vsp_debug_file("vendors/vspajax/jquery.vsp-ajax.js",'assets'),
            'addons' => vsp_debug_file('vsp-addons.js','js'),
            'plugins' => vsp_debug_file('vsp-plugins.js','js'),
            'framework' => vsp_debug_file('vsp-framework.js','js'),
        );
        
        
        $css = array(
            'bs-popover' => vsp_debug_file("libs/wpsf/assets/vendors/bootstrap/popover/popover.css",'url'),
            'bs-tooltip' => vsp_debug_file("libs/wpsf/assets/vendors/bootstrap/tooltip/tooltip.css",'url'),
            'icheck' => vsp_debug_file("libs/wpsf/assets/vendors/icheck/icheck.css",'url'),
            'select2' => vsp_debug_file("libs/wpsf/assets/vendors/select2/select2.css",'url'),
            'framework' => vsp_debug_file("vsp-framework.css",'css'),
            'owlslider' => vsp_debug_file("vendors/owlslider/owl.css",'assets'),
            'plugins' => vsp_debug_file("vsp-plugins.css",'css'),
            'addons' => vsp_debug_file("vsp-addons.css",'css'),
        );
        
        vsp_register_script('vsp-simscroll',$js['simscroll'],array('jquery'),'1.3.8',true);
        vsp_register_script('vsp-blockui',$js['blockui'],array('jquery'),'1.0.16',true);
        vsp_register_script('vsp-transition',$js['bs-transition'],array('jquery'),'3.3.7',true);
        vsp_register_script('vsp-tooltip',$js['bs-tooltip'],array('jquery'),'3.3.7',true);
        vsp_register_script('vsp-popover',$js['bs-popover'],array('vsp-tooltip'),'3.3.7',true);
        vsp_register_script('vsp-icheck',$js['icheck'],array('jquery'),'2.4.0.4',true);
        vsp_register_script('vsp-select2',$js['select2'],array('jquery'),'2 4.0.5',true);
        vsp_register_script('vsp-owlslider',$js['owlslider'],array(),'2.0',true);
        vsp_register_script('vsp-ajax',$js['vspajax'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-plugins',$js['plugins'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-addons',$js['addons'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-framework',$js['framework'],array('jquery'),'1.0',true);
        vsp_register_style('vsp-owlslider',$css['owlslider'],array(),'2.0');
        vsp_register_style('vsp-select2',$css['select2'],array(),'2 4.0.4');
        vsp_register_style('vsp-tooltip',$css['bs-tooltip'],array(),'3.3.7');
        vsp_register_style('vsp-popover',$css['bs-popover'],array(),'3.3.7');
        vsp_register_style('vsp-icheck',$css['icheck'],array(),'1.0.2');
        vsp_register_style('vsp-plugins',$css['plugins']);
        vsp_register_style('vsp-framework',$css['framework'],array(),'1.0');
        vsp_register_style('vsp-addons',$css['addons'],array(),'1.0');
        
    }
}


if(!function_exists("vsp_init_admin_notices")){
    function vsp_init_admin_notices(){
        if(vsp_is_request("admin") || vsp_is_request("ajax")){
            vsp_notices();
        }
    }
}