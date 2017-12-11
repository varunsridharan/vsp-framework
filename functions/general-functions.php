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
    function vsp_addons_extract_tags($content,$is_addons_reqplugin = false){
        if($is_addons_reqplugin === false){
            preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@',$content, $reg_shortcodes );    
        } else {
            preg_match_all( '@\[(\w[^<>&\[\]\x00-\x20=]++)@',$content, $reg_shortcodes );  #preg_match_all( '@\[([^<>&\[\]\x00-\x20=]++)@',$content, $reg_shortcodes );
        }
        return $reg_shortcodes;
    }
}

if(!function_exists('vsp_addons_extract_tags_pattern')){
    function vsp_addons_extract_tags_pattern($tags,$content,$is_addon = false){
        if(!is_array($tags)){
            $tags = array($tags);
        }
        
        foreach($tags as $i => $tag){
            $tags[$i] = str_replace("/",'\/',$tag);
        }

        $patterns = vsp_get_shortcode_regex($tags,$is_addon);
        preg_match("/$patterns/",$content,$data);
        return $data;
    }
}

if (!function_exists('vsp_current_page_url')){
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

if(!function_exists("vsp_get_time_in_seconds")){
    function vsp_get_time_in_seconds($time){
        $times = explode("_",$time);
        if(!is_array($times)){
            return $time;
        }
        
        $time_limit = $times[0];
        $type = $times[1];
        
        $time_limit = intval($time_limit);
        
        switch($type){
            case "seconds":
            case "second":
            case "sec":
                $time = $time_limit;
            break;
            case "minute":
            case "minutes":
            case "min":
                $time = $time_limit * MINUTE_IN_SECONDS;
            break;
            case "hour":
            case "hours":
            case "hrs":
                $time = $time_limit * HOUR_IN_SECONDS;
            break;
            case "days":
            case "day":
                $time = $time_limit * DAY_IN_SECONDS;
            break;
            case "weeks":
            case "week":
                $time = $time_limit * WEEK_IN_SECONDS;
            break;
                
            case "month":
            case "months":
                $time = $time_limit * MONTH_IN_SECONDS;
            break;                
            case "year":
            case "years":
                $time = $time_limit * YEAR_IN_SECONDS;
            break;
        }
        
        return intval($time);
    }
}

if(!function_exists("vsp_cdn_url")){
    function vsp_cdn_url(){
        if(defined('WP_DEBUG') && WP_DEBUG === true){
            return 'https://varunsridharan.github.io/vs-plugins-cdn-dev/';
        } else {
            return 'https://varunsridharan.github.io/vs-plugins-cdn/';
        }
    }
}

if(!function_exists("vsp_get_cdn")){
    function vsp_get_cdn($part_url,$force_decode = false){
        $part_url = ltrim($part_url,'/');
        $url = vsp_cdn_url().$part_url;
        $resource = wp_remote_get($url);
        
        if(is_wp_error($resource)){
            return $resource   ;
        } else {
            $body = wp_remote_retrieve_body( $resource );
            return json_decode($body,$force_decode);
        }
        
        return false;
    }
}

if(!function_exists("vsp_js_vars")){
    function vsp_js_vars( $object_name, $l10n,$with_script_tag = true) {
        foreach ( (array) $l10n as $key => $value ) {
            if ( !is_scalar($value) )
                continue;

            $l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
        }

        $script = "var $object_name = " . wp_json_encode( $l10n ) . ';';
        if ( !empty($after) )
            $script .= "\n$after;";

        if($with_script_tag){
            return '<script type="text/javascript"> /* <![CDATA[*/'.$script.'/*]]>*/ </script>';    
        }
        return $script;
    }
}