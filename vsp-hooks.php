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
        $li = array(
            'actual.js' => vsp_debug_file("vendors/actual/jquery.actual.js",'assets'),
            'blockui' => vsp_debug_file("vendors/blockui/jquery.blockui.js",'assets'),
            'bs-button' => vsp_debug_file("vendors/bootstrap/button/button.js",'assets'),
            'bs-tooltip' => vsp_debug_file('vendors/bootstrap/tooltip/tooltip.js','assets'),
            'bs-popover' => vsp_debug_file('vendors/bootstrap/popover/popover.js','assets'),
            'bs-transition' => vsp_debug_file('vendors/bootstrap/bootstrap-transition.js','assets'),
            'icheck' => vsp_debug_file("vendors/icheck/jquery.icheck.js",'assets'),
            'interdependencies' => vsp_debug_file("vendors/interdependencies/jquery.interdependencies.js",'assets'),
            'select2' => vsp_debug_file("vendors/select2/select2.full.js",'assets'),
            'switchery' => vsp_debug_file("vendors/switchery/switchery.js",'assets'),
            'vspajax' => vsp_debug_file("vendors/vspajax/jquery.vsp-ajax.js",'assets'),
            'woothemes-flexslider' => vsp_debug_file("vendors/woothemes/flexslider/jquery.flexslider.js",'assets'),
            'vsp-addons' => vsp_debug_file('vsp-addons.js','js'),
            'vsp-plugins' => vsp_debug_file('vsp-plugins.js','js'),
            'vsp-framework' => vsp_debug_file('vsp-framework.js','js'),
        );
        
        $cs = array(
            'bs-tooltip' => vsp_debug_file('vendors/bootstrap/tooltip/tooltip.css','assets'),
            'bs-popover' => vsp_debug_file('vendors/bootstrap/popover/popover.css','assets'),
            'icheck' => vsp_debug_file('vendors/icheck/icheck.css','assets'),
            'select2' => vsp_debug_file('vendors/select2/select2.css','assets'),
            'switchery' => vsp_debug_file("vendors/switchery/switchery.css",'assets'),
            'vsp-framework' => vsp_debug_file("vsp-framework.css",'css'),
            'woothemes-flexslider' => vsp_debug_file("vendors/woothemes/flexslider/flexslider.css",'assets'),
            'vsp-plugins' => vsp_debug_file("vsp-plugins.css",'css'),
            'vsp-addons' => vsp_debug_file("vsp-addons.css",'css'),
        );
        
        
        vsp_register_script('vsp-plugins',$li['vsp-plugins'],array('jquery'),'1.0',true);
        vsp_register_script('actual.js',$li['actual.js'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-blockui',$li['blockui'],array('jquery'),'1.0.16',true);
        vsp_register_script('vsp-transition',$li['bs-transition'],array('jquery'),'3.3.7',true);        
        vsp_register_script('vsp-button',$li['bs-button'],array('jquery'),'3.3.7',true);
        vsp_register_script('vsp-tooltip',$li['bs-tooltip'],array('jquery'),'3.3.7',true);
        vsp_register_script('vsp-popover',$li['bs-popover'],array('vsp-tooltip'),'3.3.7',true);
        vsp_register_script('vsp-icheck',$li['icheck'],array('jquery'),'2.4.0.4',true);
        vsp_register_script('vsp-interdependencies',$li['interdependencies'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-select2',$li['select2'],array('jquery'),'2 4.0.5',true);
        vsp_register_script('vsp-switchery',$li['switchery'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-ajax',$li['vspajax'],array('jquery'),'1.0',true);
        vsp_register_script('vsp-addons',$li['vsp-addons'],array('jquery'),'1.0',true);
        vsp_register_script('woothemes-flexslider',$li['woothemes-flexslider'],array(),'2.0',true);
        vsp_register_script('vsp-framework',$li['vsp-framework'],array('jquery','vsp-plugins'),'1.0',true);
        
        vsp_register_style('vsp-select2',$cs['select2'],array(),'2 4.0.4');
        vsp_register_style('vsp-tooltip',$cs['bs-tooltip'],array(),'3.3.7');
        vsp_register_style('vsp-popover',$cs['bs-popover'],array(),'3.3.7');
        vsp_register_style('vsp-icheck',$cs['icheck'],array(),'1.0.2');
        vsp_register_style('vsp-switchery',$cs['switchery'],array(),'1.0');
        vsp_register_style('vsp-plugins',$cs['vsp-plugins']);
        vsp_register_style('vsp-framework',$cs['vsp-framework'],array(),'1.0');
        vsp_register_style('vsp-addons',$cs['vsp-addons'],array(),'1.0');
        
        vsp_register_style('woothemes-flexslider',$cs['woothemes-flexslider'],array(),'2.0');

    }
}


if(!function_exists("vsp_init_admin_notices")){
    function vsp_init_admin_notices(){
        if(vsp_is_request("admin") || vsp_is_request("ajax")){
            vsp_notices();
        }
    }
}