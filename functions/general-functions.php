<?php
if(!defined("ABSPATH")){ exit; }

if(!function_exists('vsp_is_request')){
    function vsp_is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }
}

if(!function_exists('vsp_current_screen')){
    function vsp_current_screen($only_id = true){
        $screen =  get_current_screen();
        if($only_id === false){
            return $screen;
        }
        
        return isset($screen->id) ? $screen->id : false;
    }
}

if(!function_exists("vsp_is_screen")){
    function vsp_is_screen($check_screen = '',$current_screen = ''){
        if(empty($check_screen)){
            return false;
        }
        
        if(empty($current_screen)){
            $current_screen = vsp_current_screen(true);
        }
        
        
        if(is_array($check_screen)){
            if(in_array($current_screen , $check_screen)){
                return true;
            }
        }
        
        if(is_string($check_screen)){
            if($check_screen == $current_screen){
                return true;
            }
        }
        return false;
    }
}

if(!function_exists("vsp_fix_slug")){
    function vsp_fix_slug($name){
        $name = ltrim($name,' ');
        $name = ltrim($name,'_');
        $name = rtrim($name,' ');
        $name = rtrim($name,'_');
        return $name;
    }
}

if(!function_exists("vsp_addons_extract_tags")){
    function vsp_addons_extract_tags($content){
        preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@',$content, $reg_shortcodes );
        return $reg_shortcodes;
    }
}

if(!function_exists('vsp_addons_extract_tags_pattern')){
    function vsp_addons_extract_tags_pattern($tags,$content){
        if(!is_array($tags)){
            $tags = array($tags);
        }
        $patterns = get_shortcode_regex($tags);
        preg_match("/$patterns/",$content,$data);
        return $data;
    }
}