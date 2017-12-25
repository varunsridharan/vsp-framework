<?php
global $vsp_plugins,$vsp_loaded_framework,$vsp_framework_data;

$vsp_plugins = $vsp_loaded_framework = $vsp_framework_data = array();


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
        global $vsp_framework_data,$vsp_loaded_framework;
        $info = array_shift($vsp_framework_data);
        $vsp_loaded_framework = $info;
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

if(!function_exists('vsp_get_all_plugins')){
    function vsp_get_all_plugins($only_slugs = true){
        global $vsp_plugins;
        if($only_slugs === false){
            return $vsp_plugins;
        }
        return array_keys($vsp_plugins);
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

if(!function_exists("vsp_class_autoloader")){
    function vsp_class_autoloader($class) {
        $class = strtolower($class);
        if ( false === strpos( $class, 'vsp_' ) ) {
            return;
        }
        $current = str_ireplace( '_', '-', $class );
        
        $path = defined("VSP_PATH") ? VSP_PATH : __DIR__.'/';
        
        $base_path = $path.'class/class-'.$current.'.php';
        $helper_path = $path.'helper/class-'.$current.'.php';
        $settings_path = $path.'class/settings/class-'.$current.'.php';
        $addons_path = $path.'class/addons/class-'.$current.'.php';
        $tools_path = $path.'class/tools/class-'.$current.'.php';
        
        if(false !== strpos($class,'vsp_settings')){            
            if(file_exists($settings_path)){
                include($settings_path);
            } else if(file_exists($tools_path)){
                include($tools_path);
            }
        } else if(false !== strpos($class,'vsp_addons')){
            if(file_exists($addons_path)){
                include($addons_path);
            }
        } else if(file_exists($tools_path)){
            include($tools_path);
        } else if(file_exists($helper_path)){
            include($helper_path);
        } else if(file_exists($base_path)){
            include($base_path);
        }
        
    }
    spl_autoload_register('vsp_class_autoloader');
}

if(!function_exists("vsp_load_lib")){
    function vsp_load_lib($class){
        $file = str_replace('_','-',$class);
        $file = strtolower($file);
        $file .= '.php';
        
        $path = __DIR__.'/libs/';
        if(file_exists($path.$file)){
            include($path.$file);
        }
    }
}

if(!function_exists("vsp_plugin_activator")){
    function vsp_plugin_activator($options = array()){
        VSP_Activator::activate($options);
    }
}

if(!function_exists("vsp_plugin_deactivator")){
    function vsp_plugin_deactivator(){
        VSP_Deactivator::deactivate();
    }
}

if(!function_exists("vsp_plugin_dependency_deactivator")){
    function vsp_plugin_dependency_deactivator($file){
        VSP_Deactivator::dependency_deactivate($file);
    }
}