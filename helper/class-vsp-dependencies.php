<?php
if(!defined("ABSPATH")){ exit; }

if ( ! class_exists( 'VSP_Dependencies' ) ){
    class VSP_Dependencies {
		
        private static $active_plugins;
		
        public static function init() {
            self::$active_plugins = (array) get_option( 'active_plugins', array() );
            if ( is_multisite() ){
                self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
            }
        }
		
        public static function active_check($pluginToCheck = '') {
            if ( ! self::$active_plugins ) {
                self::init();
            }
            return in_array($pluginToCheck, self::$active_plugins) || array_key_exists($pluginToCheck, self::$active_plugins);
        }
    }
}