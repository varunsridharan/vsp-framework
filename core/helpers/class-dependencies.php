<?php
/**
 * WC Dependency Checker
 *
 * Checks if WooCommerce is enabled
 */
class VSP_Dependencies {

    private static $active_plugins;

    /**
     * @param $file
     * @return bool
     */
    public static function active_check($file) {
        if( ! self::$active_plugins ) {
            self::init();
        }
        return in_array($file, self::$active_plugins) || array_key_exists($file, self::$active_plugins);
    }

    public static function init() {
        self::$active_plugins = (array) get_option('active_plugins', array());
        if( is_multisite() ) {
            self::$active_plugins = array_merge(self::$active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
    }
}