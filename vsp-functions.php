<?php
if( ! defined("ABSPATH") ) {
    exit;
}

global $vsp_plugins, $vsp_loaded_framework, $vsp_framework_data;
$vsp_plugins = $vsp_loaded_framework = $vsp_framework_data = array();


/**
 * VSP Framework Specific Functions
 */
if( ! function_exists("vsp_class_autoloader") ) {
    /**
     * Autoloader Class function for vsp related Classes
     * @param string $class
     */
    function vsp_class_autoloader($class = '') {
        $class = strtolower($class);
        if( FALSE === strpos($class, 'vsp_') ) {
            return;
        }
        $current = str_ireplace('_', '-', $class);

        $path = defined("VSP_PATH") ? VSP_PATH : __DIR__ . '/';

        $base_path     = $path . 'class/class-' . $current . '.php';
        $settings_path = $path . 'class/settings/class-' . $current . '.php';
        $addons_path   = $path . 'class/addons/class-' . $current . '.php';
        $tools_path    = $path . 'class/tools/class-' . $current . '.php';
        $helper_path   = $path . 'class/helpers/class-' . $current . '.php';
        $compatibility = $path . 'class/helpers/compatibility/class-' . $current . '.php';
        $vc_fields     = $path . 'libs/visual-composer/class-' . $current . '.php';

        if( FALSE !== strpos($class, 'vc') ) {
            if( file_exists($vc_fields) ) {
                include( $vc_fields );
            }
        } else if( FALSE !== strpos($class, 'compatibility') ) {
            if( file_exists($compatibility) ) {
                include( $compatibility );
            }
        } else if( FALSE !== strpos($class, 'helper') ) {
            if( file_exists($helper_path) ) {
                include( $helper_path );
            }
        } else if( FALSE !== strpos($class, 'vsp_settings') ) {
            if( file_exists($settings_path) ) {
                include( $settings_path );
            } else if( file_exists($tools_path) ) {
                include( $tools_path );
            }
        } else if( FALSE !== strpos($class, 'vsp_addons') ) {
            if( file_exists($addons_path) ) {
                include( $addons_path );
            }
        } else if( file_exists($tools_path) ) {
            include( $tools_path );
        } else if( file_exists($base_path) ) {
            include( $base_path );
        }

    }

    spl_autoload_register('vsp_class_autoloader');
}

if( ! function_exists("vsp_register_plugin") ) {
    /**
     * Make A Copy Of Plugin instance in a array
     * @todo check if its really required funciton !
     * @param string $slug
     * @param string $instance
     */
    function vsp_register_plugin($slug = '', &$instance = '') {
        global $vsp_plugins;

        if( ! empty($slug) && ! empty($instance) ) {
            $vsp_plugins[$slug] = $instance;
        }
    }
}

if( ! function_exists('vsp_get_all_plugins') ) {
    /**
     * Returns all registered plugins instance / slug
     * @todo check if its really needed function (vsp_register_plugin)
     * @param bool $only_slugs
     * @return array
     */
    function vsp_get_all_plugins($only_slugs = TRUE) {
        global $vsp_plugins;
        if( $only_slugs === FALSE ) {
            return $vsp_plugins;
        }
        return array_keys($vsp_plugins);
    }
}

if( ! function_exists("vsp_get_plugin") ) {
    /**
     * Returns instance of a given plugin slug if instance exists
     * @param string $slug
     * @return bool
     */
    function vsp_get_plugin($slug = '') {
        global $vsp_plugins;
        if( isset($vsp_plugins[$slug]) ) {
            return $vsp_plugins[$slug];
        }
        return FALSE;
    }
}

if( ! function_exists('vsp_define') ) {
    /**
     * Defines Give Values if not defined
     * @param $key
     * @param $value
     * @return bool
     */
    function vsp_define($key, $value) {
        return defined($key) ? define($key, $value) : FALSE;
    }
}

if( ! function_exists("vsp_url") ) {
    /**
     * returns VSP Framework url
     * @param string $extra
     * @param bool   $is_url
     * @return string
     */
    function vsp_url($extra = '', $is_url = TRUE) {
        if( $is_url === TRUE ) {
            return VSP_URL . $extra;
        }
        return vsp_path($extra);
    }
}

if( ! function_exists("vsp_path") ) {
    /**
     * returns VSP Framework Full PATH
     * @param string $extra
     * @return string
     */
    function vsp_path($extra = '') {
        return VSP_PATH . $extra;
    }
}

if( ! function_exists('vsp_js') ) {
    /**
     * returns VSP Framework assets/js Path / URL base on given values
     * @param string $extra
     * @param bool   $url
     * @return string
     */
    function vsp_js($extra = '', $url = TRUE) {
        if( $url === TRUE ) {
            return vsp_url('assets/js/' . $extra);
        }
        return vsp_path('assets/js/' . $extra);
    }
}

if( ! function_exists('vsp_css') ) {
    /**
     * returns VSP Framework assets/css Path / URL base on given values
     * @param string $extra
     * @param bool   $url
     * @return string
     */
    function vsp_css($extra = '', $url = TRUE) {
        if( $url === TRUE ) {
            return vsp_url('assets/css/' . $extra);
        }
        return vsp_path('assets/css/' . $extra);
    }
}

if( ! function_exists('vsp_img') ) {
    /**
     * returns VSP Framework assets/img Path / URL base on given values
     * @param string $extra
     * @param bool   $url
     * @return string
     */
    function vsp_img($extra = '', $url = TRUE) {
        if( $url === TRUE ) {
            return vsp_url('assets/img/' . $extra);
        }
        return vsp_path('assets/img/' . $extra);
    }
}

if( ! function_exists("vsp_debug_file") ) {
    /**
     * Makes .min.css / .min.js file based on wordpress config
     * if WP_DEBUG / SCRIPT_DEBUG is set to true then it loads unminified files
     * @param      $filename
     * @param bool $makeurl
     * @param bool $is_url
     * @return mixed|null|string
     */
    function vsp_debug_file($filename, $makeurl = FALSE, $is_url = TRUE) {
        if( empty($filename) ) {
            return NULL;
        }

        if( ! ( ( defined('WP_DEBUG') && WP_DEBUG ) || ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ) ) {
            $filename = str_replace(array( '.min.css', '.min.js' ), array( '.css', '.js' ), $filename);
            $filename = str_replace('.css', '.min.css', $filename);
            $filename = str_replace('.js', '.min.js', $filename);
        }

        if( $makeurl === 'js' ) {
            return vsp_js($filename, $is_url);
        }

        if( $makeurl === 'css' ) {
            return vsp_css($filename, $is_url);
        }

        if( $makeurl === 'assets' ) {
            return vsp_url($makeurl . '/' . $filename, $is_url);
        }

        if( $makeurl === 'url' ) {
            return vsp_url($filename, $is_url);
        }

        return $filename;
    }
}

if( ! function_exists("vsp_load_file") ) {
    /**
     * Search and loads files based on the search parameter
     * @param        $search_type
     * @param string $type
     * @uses  vsp_get_file_paths
     * @example vsp_load_file("mypath/*.php")
     * @example vsp_load_file("mypath/class-*.php")
     */
    function vsp_load_file($search_type, $type = 'require') {
        foreach( vsp_get_file_paths($search_type) as $files ) {
            if( $type == 'require' ) {
                require_once( $files );
            } else if( $type == 'include' ) {
                include_once( $files );
            }
        }
    }
}

if( ! function_exists("vsp_get_file_paths") ) {
    /**
     * returns files in a given path
     * @example vsp_load_file("mypath/*.php")
     * @example vsp_load_file("mypath/class-*.php")
     * @param $path
     * @return array
     */
    function vsp_get_file_paths($path) {
        return glob($path);
    }
}

/**
 * WordPress Specific Functions
 */
if( ! function_exists('vsp_is_plugin_active') ) {
    /**
     * Checks if given plugin file is active in wordpress
     * @param string $file
     * @return bool
     */
    function vsp_is_plugin_active($file = '') {
        return VSP_Dependencies::active_check($file);
    }
}

if( ! function_exists("vsp_wc_active") ) {
    /**
     * Checks if woocommerce is active
     * in current wp instance
     * @example if(vsp_wc_active()){echo "Yes";}else{echo "No"}
     * @return bool
     */
    function vsp_wc_active() {
        return vsp_is_plugin_active('woocommerce/woocommerce.php');
    }
}

