<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("VSP_Addons_Admin") ) {
    /**
     * Class VSP_Addons_Admin
     */
    class VSP_Addons_Admin extends VSP_Addons_Core {

        public function __construct() {
            parent::__construct();
            $this->addons_list       = array();
            $this->settings_pagehook = '';
        }

        /**
         * @param $pages
         * @return mixed
         */
        public function set_settings_page($pages) {
            $pages[$this->option("addon_listing_tab_name")] = array(
                'name'          => $this->option("addon_listing_tab_name"),
                'title'         => $this->option("addon_listing_tab_title"),
                'icon'          => $this->option("addon_listing_tab_icon"),
                'callback_hook' => 'vsp_render_' . $this->slug('hook') . 'addons_list',
            );
            return $pages;
        }

        public function render_addons_page() {
            $this->addons_list = $this->search_get_addons();

            foreach( $this->addons_list as $id => $data ) {
                $this->addons_list[$id]['is_active'] = ( $this->is_active($id, $data['addon_path_md5']) === FALSE ) ? FALSE : TRUE;
                unset($this->addons_list[$id]['addon_path']);
            }

            vsp_load_script('vsp-addons');
            vsp_load_style("vsp-addons");
            wp_enqueue_style('vsp-fancybox');
            wp_enqueue_script('vsp-fancybox');
            wp_enqueue_script('plugin-install');

            add_thickbox();

            wp_localize_script('vsp-addons', 'vsp_addons_settings', array(
                'hook_slug'     => $this->slug('hook'),
                'save_slug'     => $this->slug("db"),
                'plugin_data'   => $this->addons_list,
                'default_cats'  => $this->default_cats,
                'texts'         => array(
                    'required_plugin' => __("Required Plugin", 'vsp-framework'),
                    'required_desc'   => __("Above Mentioned Plugin name with version are Tested Upto"),
                    'activate_btn'    => __("Activate"),
                    'deactivate_btn'  => __("De Activate"),
                    'admin_url'       => admin_url(),
                    'plugin_view_url' => admin_url('plugin-install.php?&isvspaddon=true&tab=plugin-information&plugin={{slug}}&pathid={{addon.addon_path_md5}}&TB_iframe=true&width=600&height=800'),
                ),
                'plugin_status' => array(
                    'exists'    => __("In Active"),
                    'notexist'  => __("Not Exist"),
                    'activated' => __("Active"),
                ),
            ));

            include __DIR__ . '/page-template.html';
        }
    }
}