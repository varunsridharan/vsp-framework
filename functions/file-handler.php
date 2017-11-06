<?php
if(!defined("ABSPATH")){ exit; }

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