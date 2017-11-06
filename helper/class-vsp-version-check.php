<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Version_Check")){
    class VSP_Version_Check {
        static $version;
        static $plugin_name;
        
        public static function activation_check( $version = '3.0',$plugin_name = '') {
            self::$version = $version;
            self::$plugin_name = $plugin_name;
            if ( ! self::compatible_version() ) {
                deactivate_plugins(PLUGIN_FILE);
                wp_die(self::get_error_msg());
            } 
        }
        
        public static function get_error_msg(){
            return self::$plugin_name.' '.__("Requires WordPress").' '.self::$version.' '.__(' or Higher!');
        }
        
        public function check_version() {
            if ( ! self::compatible_version() ) {
                if ( is_plugin_active(PLUGIN_FILE) ) {
                    deactivate_plugins(PLUGIN_FILE);
                    add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
                    if ( isset( $_GET['activate'] ) ) {
                        unset( $_GET['activate'] );
                    }
                } 
            } 
        }
        
        public function disabled_notice() {
           echo '<strong>' . self::get_error_msg() . '</strong>';
        } 

        public static function compatible_version() {
            if ( version_compare( $GLOBALS['wp_version'], self::$version, '<' ) ) {
                 return false;
             }
            return true;
        }
    }
}
