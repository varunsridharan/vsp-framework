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
    function vsp_url($extra = ''){
        return VSP_URL.$extra;
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
            return VSP_JS_URL.'/'.$extra;
        }
        return VSP_JS_PATH.$extra;
    } 
}

if(!function_exists('vsp_css')){
    function vsp_css($extra = '',$url = true){
        if($url === true){
            return VSP_CSS_URL.'/'.$extra;
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

if(!function_exists("vsp_slashit")){
    function vsp_slashit($path){
        return trailingslashit($path);
    }
}

if(!function_exists("vsp_unslashit")){
    function vsp_unslashit($path){
        return untrailingslashit($path);
    }
}

if ( ! function_exists ( 'vsp_load_js' ) ) {
    function vsp_load_js ( $filename ) {
        if (!(( defined ( 'WP_DEBUG' ) && WP_DEBUG) || (defined ( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG))) {
            $filename = str_replace ( '.js', '.min.js', $filename );
        }

        return $filename;
    }
}

if ( ! function_exists ( 'vsp_load_css' ) ) {
    function vsp_load_css ( $filename ) {
        if (!(( defined ( 'WP_DEBUG' ) && WP_DEBUG) || (defined ( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG))) {
            $filename = str_replace ( '.css', '.min.css', $filename );
        }
        return $filename;
    }
}

if ( ! function_exists ( 'vsp_current_page_url' ) ) {
    function vsp_current_page_url () {
        $pageURL = 'http';
        if ( isset( $_SERVER[ "HTTPS" ] ) AND $_SERVER[ "HTTPS" ] == "on" ) {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ( isset( $_SERVER[ "SERVER_PORT" ] ) AND $_SERVER[ "SERVER_PORT" ] != "80" ) {
            $pageURL .= $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ];
        } else {
            $pageURL .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
        }

        return $pageURL;
    }
}