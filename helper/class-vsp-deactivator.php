<?php
if(!defined("ABSPATH")){ exit; }

if ( ! class_exists( 'VSP_Deactivator' ) ){
    class VSP_Deactivator {
        public static $plugin_file;

        public static function deactivate(){}
        
        public static function dependency_deactivate($plugin_file){ 
            self::$plugin_file = $plugin_file;
            if ( is_plugin_active($plugin_file) ) {
                add_action('update_option_active_plugins', array(__CLASS__,'deactivate_dependent'));
            }
        }

        public static function deactivate_dependent(){
            deactivate_plugins(self::$plugin_file);
        }
    }
}
