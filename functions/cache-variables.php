<?php
if( ! defined("ABSPATH") ) {
    exit;
}

global $vsp_vars_data;
$vsp_vars_data = array();

if( ! function_exists("vsp_check_global_vars") ) {
    /**
     * @param string $plugin_name
     * @return mixed
     */
    function &vsp_check_global_vars($plugin_name = '') {
        $name = $plugin_name . '_plugin_data';
        if( ! isset($GLOBALS[$name]) ) {
            $GLOBALS[$name] = array();
        }
        return $GLOBALS[$name];
    }
}

if( ! function_exists("vsp_add_vars") ) {
    /**
     * @param string $plugin_name
     * @param string $key
     * @param string $values
     * @param bool   $force_add
     * @return bool
     */
    function vsp_add_vars($plugin_name = '', $key = '', $values = '', $force_add = FALSE) {
        $variable =& vsp_check_global_vars($plugin_name);
        if( isset($variable[$key]) ) {
            if( ! $force_add ) {
                return FALSE;
            }
        }
        $variable[$key] = $values;
        return TRUE;
    }
}

if( ! function_exists("vsp_vars") ) {
    /**
     * @param string $plugin_name
     * @param string $key
     * @param string $default
     * @return string
     */
    function vsp_vars($plugin_name = '', $key = '', $default = '') {
        $variable =& vsp_check_global_vars($plugin_name);
        if( ! isset($variable[$key]) ) {
            return $default;
        }
        return $variable[$key];
    }
}

if( ! function_exists("vsp_remove_vars") ) {
    /**
     * @param string $plugin_name
     * @param string $key
     * @return bool
     */
    function vsp_remove_vars($plugin_name = '', $key = '') {
        $variable =& vsp_check_global_vars($plugin_name);
        if( isset($variable[$key]) ) {
            unset($variable[$Key]);
            return TRUE;
        }
        return FALSE;
    }
}