<?php
global $vsp_plugins;

$vsp_plugins = array();

require_once(__DIR__.'/functions/plugin-handler.php');

if(!function_exists("vsp_mayby_framework_loader")){
    function vsp_mayby_framework_loader($plugin_path){
        global $vsp_framework_data;
        
        $plugin_path = rtrim($plugin_path,'/');
        $framework_path = $plugin_path.'/vsp-framework/';
        if(file_exists($framework_path.'vsp-bootstrap.php')){
            $default_headers = array (
                'Name'       => 'Framework Name',
                'Version'    => 'Version',
                'TextDomain' => 'Text Domain',
                'DomainPath' => 'Domain Path',
            );
            
            $info = get_file_data($framework_path.'vsp-bootstrap.php',$default_headers);
            $info['plugin_path'] = $plugin_path.'/';
            $info['framework_path'] = $framework_path;
            
            if(empty($vsp_framework_data)){
                $vsp_framework_data = array($info['Version'] => $info);
            } else {
                foreach($vsp_framework_data as $version => $path){
                    if(version_compare ( $version, $info[ 'Version' ], '<' )){
                        $vsp_framework_data = array($info['Version'] => $info);
                    }
                }
            }
            
        }
    }
}

if(!function_exists("vsp_framework_loader")){
    add_action("plugins_loaded",'vsp_framework_loader');
    function vsp_framework_loader(){
        global $vsp_framework_data;
        $info = array_shift($vsp_framework_data);
        require_once($info['framework_path'].'vsp-bootstrap.php');
    }
}


if(!function_exists("vsp_register_plugin")){
    function vsp_register_plugin($slug = '',&$instance = ''){
        global $vsp_plugins;
        
        if(!empty($slug) && !empty($instance)){
            $vsp_plugins[$slug] = $instance;
        }
    }
}

if(!function_exists("vsp_get_plugin")){
    function vsp_get_plugin($slug = ''){
        global $vsp_plugins;
        if(isset($vsp_plugins[$slug])){
            return $vsp_plugins[$slug];
        }
        return false;
    }
}