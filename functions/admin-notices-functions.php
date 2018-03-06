<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! function_exists('vsp_notices') ) {
    /**
     * creates a instance of a given notice class
     * @param string $type
     * @return \VSP_WP_Admin_Notices|\VSP_WP_Notice
     */
    function vsp_notices($type = '') {
        if( empty($type) ) {
            static $vsp_notices;

            if( ! isset($vsp_notices) ) {
                $vsp_notices = VSP_WP_Admin_Notices::getInstance();
            }
            return $vsp_notices;
        }

        $_instance = new VSP_WP_Notice;

        switch( $type ) {
            case 'error':
                $_instance->setType(VSP_WP_Notice::TYPE_ERROR);
            break;
            case 'update':
                $_instance->setType(VSP_WP_Notice::TYPE_UPDATED);
            break;
            case 'upgrade':
                $_instance->setType(VSP_WP_Notice::TYPE_UPDATED_NAG);
            break;
        }
        return $_instance;
    }
}

if( ! function_exists('vsp_notice') ) {
    /**
     * Updates Database With Given Notices Details
     * @param        $message
     * @param string $type
     * @param array  $args
     */
    function vsp_notice($message, $type = 'update', $args = array()) {
        $defaults  = array(
            'times'  => 1,
            'screen' => array(),
            'users'  => array(),
        );
        $args      = wp_parse_args($args, $defaults);
        $message   = str_replace('$msgID$', $args['id'], $message);
        $_instance = vsp_notices($type);
        $_instance->setContent($message)
                  ->setTimes($args['times'])
                  ->setScreens($args['screen'])
                  ->addUsers($args['users']);
        vsp_notices()->addNotice($_instance);
    }
}

if( ! function_exists('vsp_notice_error') ) {
    /**
     * Creates a error notice instances and saves in DB
     * @param        $message
     * @param string $id
     * @param int    $times
     * @param array  $screen
     * @param array  $args
     */
    function vsp_notice_error($message, $times = 1, $screen = array(), $args = array()) {
        $args['times']  = $times;
        $args['screen'] = $screen;
        if( isset($args['on_ajax']) && $args['on_ajax'] === FALSE && vsp_is_ajax() ) {
            return;
        }
        vsp_notice($message, 'error', $args);
    }
}

if( ! function_exists('vsp_notice_update') ) {
    /**
     * Creates a error update instances and saves in DB
     * @param        $message
     * @param string $id
     * @param int    $times
     * @param array  $screen
     * @param array  $args
     */
    function vsp_notice_update($message, $times = 1, $screen = array(), $args = array()) {
        $args['times']  = $times;
        $args['screen'] = $screen;
        if( isset($args['on_ajax']) && $args['on_ajax'] === FALSE && vsp_is_ajax() ) {
            return;
        }
        vsp_notice($message, 'update', $args);
    }
}

if( ! function_exists('vsp_notice_upgrade') ) {
    /**
     * Creates a upgrade notice instances and saves in DB
     * @param        $message
     * @param string $id
     * @param int    $times
     * @param array  $screen
     * @param array  $args
     */
    function vsp_notice_upgrade($message, $times = 1, $screen = array(), $args = array()) {
        $args['times']  = $times;
        $args['screen'] = $screen;
        if( isset($args['on_ajax']) && $args['on_ajax'] === FALSE && vsp_is_ajax() ) {
            return;
        }
        vsp_notice($message, 'upgrade', $args);
    }
}


if( ! function_exists("vsp_js_alert") ) {
    function vsp_js_alert($title = '', $text = '', $type = '', $options = array()) {
        $defaults = array(
            'title'   => $title,
            'text'    => $text,
            'icon'    => $type,
            'buttons' => array(
                'confirm' => array(
                    'className' => 'btn-primary',
                    'text'      => __("Ok"),
                ),
            ),
            'after'   => '',
        );

        $opts        = wp_parse_args($options, $defaults);
        $opts        = array_filter($opts);
        $after       = isset($opts['after']) ? $opts['after'] : '';
        $return_html = '';
        $name        = 'swal' . rand(1, 100);
        $return_html .= vsp_js_vars($name, $opts, FALSE);
        $return_html .= 'swal(' . $name . ')';

        if( ! empty($after) ) {
            $return_html .= '.then((value)=> {' . $after . '})';
        }

        $return_html .= ';';
        return $return_html;
    }
}

if( ! function_exists("vsp_js_alert_success") ) {
    function vsp_js_alert_success($title = '', $text = '', $options = array()) {
        return vsp_js_alert($title, $text, 'success', $options);
    }
}

if( ! function_exists("vsp_js_alert_error") ) {
    function vsp_js_alert_error($title = '', $text = '', $options = array()) {
        return vsp_js_alert($title, $text, 'error', $options);
    }
}

if( ! function_exists("vsp_js_alert_warning") ) {
    function vsp_js_alert_warning($title = '', $text = '', $options = array()) {
        return vsp_js_alert($title, $text, 'warning', $options);
    }
}

if( ! function_exists("vsp_js_alert_info") ) {
    function vsp_js_alert_info($title = '', $text = '', $options = array()) {
        return vsp_js_alert($title, $text, 'info', $options);
    }
}
