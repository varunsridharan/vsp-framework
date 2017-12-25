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
            return vsp_url('assets/js/'.$extra);
        }
        return vsp_path('assets/js/'.$extra);
    } 
}

if(!function_exists('vsp_css')){
    function vsp_css($extra = '',$url = true){
        if($url === true){
            return vsp_url('assets/css/'.$extra);
        }
        return vsp_path('assets/css/'.$extra);
    } 
}

if(!function_exists('vsp_img')){
    function vsp_img($extra = '',$url = true){
        if($url === true){
            return vsp_url('assets/img/'.$extra);
        }
        return vsp_path('assets/img/'.$extra);
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
        
        if($makeurl === 'url'){
            return vsp_url($filename,$is_url);
        }
        
        return $filename;
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