<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Activator")){
    class VSP_Activator{
        public static $path = '';

        public function __construct(){
            self::$path = plugin_dir_path( __FILE__ );
        }

        public static function activate($options = array()){
            $path = self::$path;

            $defaults = array(
                'dependency' => 'woocommerce/woocommerce.php',
                'required_wp_version' => '3.7',
                'plugin_file' => '',
                'dependency_message' => '',
            );

            $options = wp_parse_args($options,$defaults);

            require_once($path.'class-vsp-version-check.php');
            require_once($path.'class-vsp-dependencies.php');
            
            if(VSP_Dependencies::active_check($options['dependency'])){
                VSP_Version_Check::activation_check($options['required_wp_version']);
            } else {
                if(is_plugin_active($options['plugin_file'])){
                    deactivate_plugins($options['plugin_file']);
                }
                wp_die($options['dependency_message']);
            }
        }
    }
}