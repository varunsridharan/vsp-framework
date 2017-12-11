<?php
if(!defined("ABSPATH")){ exit; }

if(!function_exists('vsp_define')){
    function vsp_define($key,$value){
        if(!defined($key)){
            define($key,$value);
        }
    }
}

if(!function_exists("vsp_url")){
    function vsp_url($extra = '',$is_url = true){
        if($is_url === true){
            return VSP_URL.$extra;
        }
        return vsp_path($extra);
    }
}

if(!function_exists("vsp_path")){
    function vsp_path($extra = ''){
        return VSP_PATH.$extra;
    }
}

if(!function_exists('vsp_js')){
    function vsp_js($extra = '',$url = true){
        if($url === true){
            return VSP_JS_URL.$extra;
        }
        return VSP_JS_PATH.$extra;
    } 
}

if(!function_exists('vsp_css')){
    function vsp_css($extra = '',$url = true){
        if($url === true){
            return VSP_CSS_URL.$extra;
        }
        return VSP_CSS_URL.$extra;
    } 
}

if(!function_exists('vsp_img')){
    function vsp_img($extra = '',$url = true){
        if($url === true){
            return VSP_IMG_URL.'/'.$extra;
        }
        return VSP_IMG_PATH.$extra;
    } 
}

if(!function_exists("vsp_debug_file")){
    function vsp_debug_file($filename,$makeurl = false,$is_url = true){
        if(empty($filename)){return null;}
        
        if (!(( defined ( 'WP_DEBUG' ) && WP_DEBUG) || (defined ( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG))) {
            $filename = str_replace(array('.min.css','.min.js'),array('.css','.js'),$filename);
            $filename = str_replace ( '.css', '.min.css', $filename );
            $filename = str_replace ( '.js', '.min.js', $filename );
        }
        
        
        if($makeurl === 'js'){                
            return vsp_js($filename,$is_url);
        }
        
        if($makeurl === 'css'){
            return vsp_css($filename,$is_url);
        }

        if($makeurl === 'assets'){
            return vsp_url($makeurl.'/'.$filename,$is_url);
        }
        
        return $filename;
    }
}

if(!function_exists('vsp_load_class')){
    function vsp_load_class($slug = ''){
        $files = array(
            'activator' => 'helper/class-vsp-activator.php',
            'deactivator' => 'helper/class-vsp-deactivator.php',
            'dependencies' => 'helper/class-vsp-dependencies.php',
            'version-check' => 'helper/class-vsp-version-check.php',
            
            'framework' => 'class/class-framework.php',
            'framework-handler' => 'class/class-framework-class-handler.php',
            'framework-init' => 'class/class-framework-init.php',
            'framework-options' => 'class/class-framework-options.php',
            'famework-ajax' => 'class/class-framework-core-ajax.php',
            
            'addons' => 'class/addons/class-addons.php',
            'addons-admin' => 'class/addons/class-addons-admin.php',
            'addons-core' => 'class/addons/class-addons-core.php',
            'addons-view' => 'class/addons/class-addons-detailed-view.php',
            'addons-filemeta' => 'class/addons/class-addons-filemeta.php',

            'settings-fields' => 'class/settings/class-settings-fields.php',
            'settings-handler' => 'class/settings/class-settings-handler.php',
            'settings-init' => 'class/settings/class-settings-init.php',
            
            'admin-notices' => 'class/tools/class-admin-notices.php',
            'settings-status-page' => 'class/tools/class-settings-status-page.php',
            'status-page-handler' => 'class/tools/class-site-status-report.php',
        );
        
    }
}

if(!function_exists("vsp_load_file")){
    function vsp_load_file($path,$type = 'require'){
        foreach( glob( $path ) as $files ){
            if($type == 'require'){ 
                require_once( $files ); 
            } else if($type == 'include'){ 
                include_once( $files ); 
            }
        } 
    }
}

if(!function_exists("vsp_get_file_paths")){
    function vsp_get_file_paths($path){
        return glob($path);
    }
}