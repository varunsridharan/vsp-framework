<?php

if( ! function_exists("vsp_cache_options") ) {
    function vsp_cache_options() {
        $exSections = get_option("vsp_settings_sections");
        if( empty($exSections) ) {
            return;
        }

        $is_modified = FALSE;
        $active_Plugins = vsp_get_all_plugins();
        foreach( $exSections as $plugin => $sections ) {
            if( ! in_array($plugin, $active_Plugins) ) {
                unset($exSections[$plugin]);
                $is_modified = TRUE;
                continue;
            }

            $save_arr = array();
            foreach( $sections as $id ) {
                $option = get_option($id);
                if( $option === FALSE || ! is_array($option) ) {
                    continue;
                }

                $save_arr = array_merge($save_arr, $option);
            }

            vsp_add_vars($plugin, 'settings', $save_arr, TRUE);
        }

        if( $is_modified === TRUE ) {
            update_option('vsp_settings_sections', $exSections);
        }
    }
}


if( ! function_exists("vsp_settings_save_sections") ) {
    /**
     * @param string $plugin_slug
     * @param string $db_slug
     * @param        $sections
     */
    function vsp_settings_save_sections($plugin_slug = '', $db_slug = '', $sections) {
        $exSections = get_option("vsp_settings_sections");
        if( empty($exSections) ) {
            $exSections = array();
        }

        if( isset($exSections[$plugin_slug]) ) {
            if( count($exSections[$plugin_slug]) == count($sections) ) {
                return;
            }
        }

        $section_ids = wp_list_pluck($sections, 'id', 'id');
        $db_slug = vsp_fix_slug($db_slug);
        $r = array();
        foreach( $section_ids as $id => $i ) {
            $r[] = $db_slug . '_' . $i;
        }

        $exSections[$plugin_slug] = $r;
        update_option('vsp_settings_sections', $exSections);
    }
}

if( ! function_exists("vsp_option") ) {
    /**
     * @param string $plugin_name
     * @param string $option_name
     * @param string $default
     * @return string
     */
    function vsp_option($plugin_name = '', $option_name = '', $default = '') {
        $options = vsp_vars($plugin_name, 'settings', array());
        if( ! empty($options) ) {
            if( $option_name === 'all' ) {
                return $options;
            }
            if( isset($options[$option_name]) ) {
                return $options[$option_name];
            }
        }

        return $default;
    }
}