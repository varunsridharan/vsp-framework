<?php
global $vsp_plugins, $vsp_loaded_framework, $vsp_framework_data;

$vsp_plugins = $vsp_loaded_framework = $vsp_framework_data = array();


if( ! function_exists("vsp_mayby_framework_loader") ) {
    /**
     * Adds Passed Plugin path to the list array which later used to compare and
     * load the framework from a plugin which has the latest version of framework
     * @param $plugin_path
     */
    function vsp_mayby_framework_loader($plugin_path = '', $framework_path = '/vsp-framework/') {
        global $vsp_framework_data;
        $plugin_path    = rtrim($plugin_path, '/');
        $framework_path = $plugin_path . $framework_path;
        if( file_exists($framework_path . 'vsp-bootstrap.php') ) {
            $default_headers        = array(
                'Name'       => 'Framework Name',
                'Version'    => 'Version',
                'TextDomain' => 'Text Domain',
                'DomainPath' => 'Domain Path',
            );
            $info                   = get_file_data($framework_path . 'vsp-bootstrap.php', $default_headers);
            $info['plugin_path']    = $plugin_path . '/';
            $info['framework_path'] = $framework_path;
            if( empty($vsp_framework_data) ) {
                $vsp_framework_data = array( $info['Version'] => $info );
            } else {
                foreach( $vsp_framework_data as $version => $path ) {
                    if( version_compare($version, $info['Version'], '<') ) {
                        $vsp_framework_data = array( $info['Version'] => $info );
                    }
                }
            }
        }
    }
}

if( ! function_exists("vsp_framework_loader") ) {
    add_action("plugins_loaded", 'vsp_framework_loader');
    /**
     * Loads VSP Framework on plugins_loaded hook
     * @uses $vsp_framework_data - contains latest framework version path and general info
     */
    function vsp_framework_loader() {
        global $vsp_framework_data, $vsp_loaded_framework;
        $info                 = array_shift($vsp_framework_data);
        $vsp_loaded_framework = $info;
        require_once( $info['framework_path'] . 'vsp-bootstrap.php' );
    }
}