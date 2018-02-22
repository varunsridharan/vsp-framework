<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 19-02-2018
 * Time: 11:45 AM
 */

require_once( __DIR__ . '/functions.php' );

class VSP_VC_Elements_Loader {

    public function __construct($args = array()) {
        $this->instances = array();
        $this->lookupKeys = array();
        $this->args = wp_parse_args($args, array(
            'class_prefix'      => '',
            'filename_prefix'   => '',
            'vc_path'           => '',
            'exclude'           => array(),
            'callback_function' => array(),
            'callback_hook'     => 'vc_fields_loaded',
        ));

        $this->loader();
    }

    public function loader() {
        if( empty(array_filter($this->args)) ) {
            return FALSE;
        }

        $files = glob($this->args['vc_path'] . '*.php');

        foreach( $files as $file ) {
            if( in_array($files, array( '.', '..' )) ) {
                continue;
            } else if( in_array($files, $this->args['exclude']) ) {
                continue;
            }

            $class_name = untrailingslashit(str_replace($this->args['vc_path'], '', $file));
            $class_name = ltrim($class_name, '/');
            $class_name = rtrim($class_name, '/');
            $class_name = str_replace(array( $this->args['filename_prefix'], '.php', '-', ), array(
                '',
                '',
                '_',
            ), $class_name);
            $class_name = $this->args['class_prefix'] . $class_name;

            require_once( $file );
            $return = call_user_func($this->args['callback_function'], $class_name);
            $this->instances($return);
        }
        do_action($this->args['callback_hook']);
    }

    public function instances($instance) {
        if( is_string($instance) ) {
            if( isset($this->lookupKeys[$instance]) ) {
                $instance = $this->lookupKeys[$instance];
            }

            if( isset($this->instances[$instance]) ) {
                return $this->instances[$instance];
            }

            return FALSE;
        } else if( is_object($instance) ) {
            $this->instances[get_class($instance)] = get_class($instance);
            $this->lookupKeys[$instance->base] = get_class($instance);
        }
        return FALSE;
    }
}