<?php
global $vsp_plugins, $vsp_loaded_framework, $vsp_framework_data;

$vsp_plugins = $vsp_loaded_framework = $vsp_framework_data = array();


if( ! function_exists("vsp_mayby_framework_loader") ) {
    /**
     * @param $plugin_path
     */
    function vsp_mayby_framework_loader($plugin_path) {
        global $vsp_framework_data;
        $plugin_path = rtrim($plugin_path, '/');
        $framework_path = $plugin_path . '/vsp-framework/';
        if( file_exists($framework_path . 'vsp-bootstrap.php') ) {
            $default_headers = array(
                'Name'       => 'Framework Name',
                'Version'    => 'Version',
                'TextDomain' => 'Text Domain',
                'DomainPath' => 'Domain Path',
            );

            $info = get_file_data($framework_path . 'vsp-bootstrap.php', $default_headers);
            $info['plugin_path'] = $plugin_path . '/';
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
    function vsp_framework_loader() {
        global $vsp_framework_data, $vsp_loaded_framework;
        $info = array_shift($vsp_framework_data);
        $vsp_loaded_framework = $info;
        require_once( $info['framework_path'] . 'vsp-bootstrap.php' );
    }
}