<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 08:39 AM
 */

final class VSP_Autoloader {

    private static $_integrations = array(
        'wpsf'            => 'wpsf.php',
        'visual-composer' => 'visual-composer.php',
    );

    private static $_libs = array(
        'wp-async'     => 'async.php',
        'vs-transient' => 'vs-transient.php',
        'wpsf'         => 'wpsf.php',
        'wpreview'     => 'review-me.php',
        'wpallimport'  => 'wpallimport.php',
    );

    public static function load($class = '') {
        if( strpos($class, 'VSP_') !== FALSE ) {
            $filename = str_replace('_', '-', strtolower($class));

            if( substr($filename, 0, strlen('vsp-')) == 'vsp-' ) {
                $filename = substr($filename, strlen('vsp_'));
            }

            $prefix = 'class-';
            $surfix = '.php';

            if( strpos($filename, 'class') !== FALSE ) {
                $prefix = '';
            } else if( strpos($filename, 'interface') !== FALSE ) {
                $filename = str_replace('interface', '', $filename);
                $prefix   = 'interface-';
            } else if( strpos($filename, 'trait') !== FALSE ) {
                $filename = str_replace('trait', '', $filename);
                $prefix   = 'trait-';
            }

            $filename = $prefix . self::fix_filename($filename) . $surfix;
            self::check_load($class, $filename);
        }
    }

    public static function fix_filename($file_name = '') {
        $file_name = trim($file_name, '-');
        $file_name = trim($file_name, '_');
        $file_name = trim($file_name, ' ');
        return $file_name;
    }

    public static function check_load($classname = '', $file_name = '') {

        if( file_exists(VSP_CORE . 'abstract/' . $file_name) ) {
            require_once( VSP_CORE . 'abstract/' . $file_name );
        } else if( file_exists(VSP_CORE . 'trait/' . $file_name) ) {
            require_once( VSP_CORE . 'trait/' . $file_name );
        } else if( file_exists(VSP_CORE . 'interface/' . $file_name) ) {
            require_once( VSP_CORE . 'interface/' . $file_name );
        } else if( file_exists(VSP_CORE . 'helpers/' . $file_name) ) {
            require_once( VSP_CORE . 'helpers/' . $file_name );
        } else if( file_exists(VSP_CORE . 'helpers/woocommerce/' . $file_name) ) {
            require_once( VSP_CORE . 'helpers/woocommerce/' . $file_name );
        } else if( file_exists(VSP_CORE . '' . $file_name) ) {
            require_once( VSP_CORE . '' . $file_name );
        } else if( file_exists(VSP_CORE . 'modules/addons/' . $file_name) ) {
            require_once( VSP_CORE . 'modules/addons/' . $file_name );
        } else if( file_exists(VSP_CORE . 'modules/admin-notices/' . $file_name) ) {
            require_once( VSP_CORE . 'modules/admin-notices/' . $file_name );
        } else if( file_exists(VSP_CORE . 'modules/settings/' . $file_name) ) {
            require_once( VSP_CORE . 'modules/settings/' . $file_name );
        } else if( file_exists(VSP_CORE . 'modules/' . $file_name) ) {
            require_once( VSP_CORE . 'modules/' . $file_name );
        } else if( has_action('vsp_load_' . $classname) ) {
            do_action('vsp_load_' . $classname, $classname, $file_name);
        }
    }

    public static function integration($integration = '') {
        $integration = strtolower($integration);
        if( isset(self::$_integrations[$integration]) ) {
            if( file_exists(self::integration_path() . self::$_integrations[$integration]) ) {
                require_once( self::integration_path() . self::$_integrations[$integration] );
                return TRUE;
            } else if( has_action('vsp_integration_' . $integration) ) {
                do_action('vsp_integration_' . $integration);
                return TRUE;
            }
        }
        return FALSE;
    }

    public static function integration_path() {
        return VSP_PATH . 'integrations/';
    }

    public static function library($lib) {
        $lib = strtolower($lib);
        if( isset(self::$_libs[$lib]) ) {
            if( file_exists(self::lib_path() . self::$_libs[$lib]) ) {
                require_once( self::lib_path() . self::$_libs[$lib] );
                return TRUE;
            } else if( has_action('vsp_lib_' . $lib) ) {
                do_action('vsp_lib_' . $lib);
                return TRUE;
            }
        }
        return FALSE;
    }

    public static function lib_path() {
        return VSP_PATH . 'libs/';
    }

    public static function get_libs() {
        return self::$_libs;
    }

    public static function get_integrations() {
        return self::$_integrations;
    }
}