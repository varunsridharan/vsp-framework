<?php
if(!defined("ABSPATH")){ exit; }

if(!function_exists('vsp_notices')){
    function vsp_notices($type = ''){
        if(!class_exists("VSP_Admin_Notice",false)){
            require_once(VSP_PATH.'class/tools/class-vsp-admin-notice.php');
        }
        
        switch($type){
            case 'error':
                return new VSP_Admin_Notices_Error;
                break;
            case 'update':
                return new VSP_Admin_Notices_Updated;
                break;
            case 'upgrade':
                return new VSP_Admin_Notices_UpdateNag;
                break;
            default :
                static $vsp_notices;

                if(!isset($vsp_notices)){
                    $vsp_notices = VSP_Admin_Notice::instance();
                }
                return $vsp_notices;
                break;
        }
        return false;
    }
}

if(!function_exists('vsp_remove_notice')){
    function vsp_remove_notice($id){
        vsp_notices()->deleteNotice($id);
        return true;
    }
}

if(!function_exists('vsp_notice')){
    function vsp_notice( $message, $type = 'update',$args = array()) {
        $defaults = array('times' => 1,'screen' => array(),'users' => array(), 'wraper' => true,'id'=> $type.'-'.uniqid());    
        $args = wp_parse_args( $args, $defaults );
        $message = str_replace('$msgID$',$args['id'],$message);
        $_instance = vsp_notices($type);
        $_instance->setContent($message)->set_id($args['id'])->setTimes($args['times'])->setScreen($args['screen'])->setUsers($args['users'])->setWrapper($args['wraper']);
        vsp_notices()->addNotice($_instance);
    }
}

if(!function_exists('vsp_notice_error')){
    function vsp_notice_error( $message,$id = '', $times = 1,  $screen = array(),$args = array()) {
        $args['id'] = $id;
        $args['times'] = $times;
        $args['screen'] = $screen;
        if(isset($args['on_ajax']) && $args['on_ajax'] === false && vsp_is_ajax()){
            return;
        }
        vsp_notice($message,'error',$args);
    }
}

if(!function_exists('vsp_notice_update')){
    function vsp_notice_update( $message,$id = '', $times = 1,  $screen = array(),$args = array()) {
        $args['id'] = $id;
        $args['times'] = $times;
        $args['screen'] = $screen;
        if(isset($args['on_ajax']) && $args['on_ajax'] === false && vsp_is_ajax()){
            return;
        }
        vsp_notice($message,'update',$args);
    }
}

if(!function_exists('vsp_notice_upgrade')){
    function vsp_notice_upgrade( $message,$id = '', $times = 1,  $screen = array(),$args = array()) {
        $args['id'] = $id;
        $args['times'] = $times;
        $args['screen'] = $screen;
        if(isset($args['on_ajax']) && $args['on_ajax'] === false && vsp_is_ajax()){
            return;
        }
        vsp_notice($message,'upgrade',$args);
    }
}