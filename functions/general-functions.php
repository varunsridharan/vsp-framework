<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! function_exists('vsp_ajax_action') ) {
    /**
     * @return bool
     */
    function vsp_ajax_action() {
        if( vsp_is_request('ajax') ) {
            return ( isset($_REQUEST['action']) ) ? $_REQUEST['action'] : FALSE;
        }

        return FALSE;
    }
}

if( ! function_exists('vsp_is_ajax') ) {
    /**
     * @param string $action
     * @return bool
     */
    function vsp_is_ajax($action = '') {
        if( empty($action) ) {
            return vsp_is_request('ajax');
        }

        return ( vsp_ajax_action() !== FALSE && vsp_ajax_action() === $action ) ? TRUE : FALSE;
    }
}

if( ! function_exists('vsp_is_cron') ) {
    /**
     * @return bool
     */
    function vsp_is_cron() {
        return vsp_is_request('cron');
    }
}

if( ! function_exists('vsp_is_admin') ) {
    /**
     * @return bool
     */
    function vsp_is_admin() {
        return vsp_is_request('admin');
    }
}

if( ! function_exists('vsp_is_frontend') ) {
    /**
     * @return bool
     */
    function vsp_is_frontend() {
        return vsp_is_request('frontend');
    }
}

if( ! function_exists('vsp_is_request') ) {
    /**
     * @param $type
     * @return bool
     */
    function vsp_is_request($type) {
        switch( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return ( ! is_admin() || defined('DOING_AJAX') ) && ! defined('DOING_CRON');
        }
        return FALSE;
    }
}

if( ! function_exists('vsp_current_screen') ) {
    /**
     * @param bool $only_id
     * @return bool|null|string|\WP_Screen
     */
    function vsp_current_screen($only_id = TRUE) {
        $screen = get_current_screen();
        if( $only_id === FALSE ) {
            return $screen;
        }

        return isset($screen->id) ? $screen->id : FALSE;
    }
}

if( ! function_exists("vsp_is_screen") ) {
    /**
     * @param string $check_screen
     * @param string $current_screen
     * @return bool
     */
    function vsp_is_screen($check_screen = '', $current_screen = '') {
        if( empty($check_screen) ) {
            return FALSE;
        }

        if( empty($current_screen) ) {
            $current_screen = vsp_current_screen(TRUE);
        }


        if( is_array($check_screen) ) {
            if( in_array($current_screen, $check_screen) ) {
                return TRUE;
            }
        }

        if( is_string($check_screen) ) {
            if( $check_screen == $current_screen ) {
                return TRUE;
            }
        }
        return FALSE;
    }
}

if( ! function_exists("vsp_fix_slug") ) {
    /**
     * @param $name
     * @return string
     */
    function vsp_fix_slug($name) {
        $name = ltrim($name, ' ');
        $name = ltrim($name, '_');
        $name = rtrim($name, ' ');
        $name = rtrim($name, '_');
        return $name;
    }
}

if( ! function_exists("vsp_addons_extract_tags") ) {
    /**
     * @param      $content
     * @param bool $is_addons_reqplugin
     * @return mixed
     */
    function vsp_addons_extract_tags($content, $is_addons_reqplugin = FALSE) {
        if( $is_addons_reqplugin === FALSE ) {
            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $reg_shortcodes);
        } else {
            preg_match_all('@\[(\w[^<>&\[\]\x00-\x20=]++)@', $content, $reg_shortcodes);  #preg_match_all( '@\[([^<>&\[\]\x00-\x20=]++)@',$content, $reg_shortcodes );
        }
        return $reg_shortcodes;
    }
}

if( ! function_exists('vsp_addons_extract_tags_pattern') ) {
    /**
     * @param      $tags
     * @param      $content
     * @param bool $is_addon
     * @return mixed
     */
    function vsp_addons_extract_tags_pattern($tags, $content, $is_addon = FALSE) {
        if( ! is_array($tags) ) {
            $tags = array( $tags );
        }

        foreach( $tags as $i => $tag ) {
            $tags[$i] = str_replace("/", '\/', $tag);
        }

        $patterns = vsp_get_shortcode_regex($tags, $is_addon);
        preg_match("/$patterns/", $content, $data);
        return $data;
    }
}

if( ! function_exists('vsp_current_page_url') ) {
    /**
     * @return string
     */
    function vsp_current_page_url() {
        $pageURL = 'http';
        if( isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on" ) {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if( isset($_SERVER["SERVER_PORT"]) AND $_SERVER["SERVER_PORT"] != "80" ) {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }
}

if( ! function_exists("vsp_get_time_in_seconds") ) {
    /**
     * @param $time
     * @return float|int
     */
    function vsp_get_time_in_seconds($time) {
        $times = explode("_", $time);
        if( ! is_array($times) ) {
            return $time;
        }

        $time_limit = $times[0];
        $type = $times[1];

        $time_limit = intval($time_limit);

        switch( $type ) {
            case "seconds":
            case "second":
            case "sec":
                $time = $time_limit;
            break;
            case "minute":
            case "minutes":
            case "min":
                $time = $time_limit * MINUTE_IN_SECONDS;
            break;
            case "hour":
            case "hours":
            case "hrs":
                $time = $time_limit * HOUR_IN_SECONDS;
            break;
            case "days":
            case "day":
                $time = $time_limit * DAY_IN_SECONDS;
            break;
            case "weeks":
            case "week":
                $time = $time_limit * WEEK_IN_SECONDS;
            break;

            case "month":
            case "months":
                $time = $time_limit * MONTH_IN_SECONDS;
            break;
            case "year":
            case "years":
                $time = $time_limit * YEAR_IN_SECONDS;
            break;
        }

        return intval($time);
    }
}

if( ! function_exists("vsp_cdn_url") ) {
    /**
     * @return string
     */
    function vsp_cdn_url() {
        if( defined('WP_DEBUG') && WP_DEBUG === TRUE ) {
            return 'https://varunsridharan.github.io/vs-plugins-cdn-dev/';
        } else {
            return 'https://varunsridharan.github.io/vs-plugins-cdn/';
        }
    }
}

if( ! function_exists("vsp_get_cdn") ) {
    /**
     * @param      $part_url
     * @param bool $force_decode
     * @return array|mixed|object|\WP_Error
     */
    function vsp_get_cdn($part_url, $force_decode = FALSE) {
        $part_url = ltrim($part_url, '/');
        $url = vsp_cdn_url() . $part_url;
        $resource = wp_remote_get($url);

        if( is_wp_error($resource) ) {
            return $resource;
        } else {
            $body = wp_remote_retrieve_body($resource);
            return json_decode($body, $force_decode);
        }
    }
}

if( ! function_exists("vsp_js_vars") ) {
    /**
     * @param      $object_name
     * @param      $l10n
     * @param bool $with_script_tag
     * @return string
     */
    function vsp_js_vars($object_name, $l10n, $with_script_tag = TRUE) {
        foreach( (array) $l10n as $key => $value ) {
            if( ! is_scalar($value) )
                continue;

            $l10n[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
        }

        $script = "var $object_name = " . wp_json_encode($l10n) . ';';
        if( ! empty($after) )
            $script .= "\n$after;";

        if( $with_script_tag ) {
            return "<script type=\"text/javascript\"> $script </script>";
        }
        return $script;
    }
}

if( ! function_exists("vsp_placeholder_img") ) {
    /**
     * @return mixed
     */
    function vsp_placeholder_img() {
        return apply_filters('vsp_placeholder_img', vsp_img('noimage.png'));
    }
}

if( ! function_exists('vsp_is_user_role') ) {
    /**
     * @param null $role
     * @param null $current_role
     * @return bool
     */
    function vsp_is_user_role($role = NULL, $current_role = NULL) {
        if( in_array($role, array( 'logedout', 'loggedout', 'visitor' )) ) {
            $role = 'visitor';
        }

        if( $current_role === NULL ) {
            $current_role = vsp_get_current_user(TRUE);
        }

        return ( $role === $current_role ) ? TRUE : FALSE;
    }
}

if( ! function_exists('vsp_get_current_user') ) {
    /**
     * @param bool $user_role_only
     * @return mixed|string|\WP_User
     */
    function vsp_get_current_user($user_role_only = TRUE) {
        $user_role = wp_get_current_user();
        if( $user_role_only === TRUE ) {
            $user_roles = $user_role->roles;
            $user_role = array_shift($user_roles);
            if( $user_role == NULL ) {
                $user_role = 'visitor';
            }
        }

        return $user_role;
    }
}

if( ! function_exists('vsp_wp_user_roles') ) {
    /**
     * @return array
     */
    function vsp_wp_user_roles() {
        $all_roles = array();
        if( function_exists('wp_roles') ) {
            $all_roles = wp_roles()->roles;
        }
        return $all_roles;
    }
}

if( ! function_exists('vsp_get_user_roles') ) {
    /**
     * @return array|mixed
     */
    function vsp_get_user_roles() {
        $user_roles = vsp_wp_user_roles();
        $user_roles['visitor'] = array( 'name' => __('Visitor / Logged-Out User', 'vsp-framework') );
        $user_roles = apply_filters('wc_rbp_wp_user_roles', $user_roles);
        return $user_roles;
    }
}

if( ! function_exists('vsp_user_roles_as_options') ) {
    /**
     * @param bool $only_slug
     * @return array
     */
    function vsp_user_roles_as_options($only_slug = FALSE) {
        $return = array();
        foreach( vsp_get_user_roles() as $slug => $data ) {
            $return[$slug] = $data['name'];
        }
        return ( $only_slug === TRUE ) ? array_keys($return) : $return;
    }
}

if( ! function_exists('vsp_filter_user_roles') ) {
    /**
     * @example This function will filter vsp_user_roles_as_options function and provide only the given user role slug values
     * @param array $required
     * @return array
     */
    function vsp_filter_user_roles($required = array()) {

        $existing = vsp_user_roles_as_options(FALSE);
        if( ! is_array($required) ) {
            return $existing;
        }
        foreach( $existing as $slug => $name ) {
            if( ! in_array($slug, $required) ) {
                unset($existing[$slug]);
            }
        }
        return $existing;
    }
}

if( ! function_exists('vsp_array_insert_before') ) {
    /*
     * Inserts a new key/value before the key in the array.
     * @param $key The key to insert before.
     * @param $array An array to insert in to.
     * @param $new_key The key to insert.
     * @param $new_value An value to insert.
     * @return The new array if the key exists, FALSE otherwise.
     * @see array_insert_after()
     */
    function vsp_array_insert_before($key, array &$array, $new_key, $new_value) {
        if( array_key_exists($key, $array) ) {
            $new = array();
            foreach( $array as $k => $value ) {
                if( $k === $key ) {
                    $new[$new_key] = $new_value;
                }
                $new[$k] = $value;
            }
            return $new;
        }
        return FALSE;
    }
}

if( ! function_exists('vsp_array_insert_after') ) {
    /*
     * Inserts a new key/value after the key in the array.
     * @param $key The key to insert after.
     * @param $array An array to insert in to.
     * @param $new_key The key to insert.
     * @param $new_value An value to insert.
     * @return The new array if the key exists, FALSE otherwise.
     * @see array_insert_before()
     */
    function vsp_array_insert_after($key, array &$array, $new_key, $new_value) {
        if( array_key_exists($key, $array) ) {
            $new = array();
            foreach( $array as $k => $value ) {
                $new[$k] = $value;
                if( $k === $key ) {
                    $new[$new_key] = $new_value;
                }
            }
            return $new;
        }
        return FALSE;
    }
}