<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Addons")){
    class VSP_Addons extends VSP_Addons_Admin {
        
        protected $user_options = array();
        
        protected $default_options = array(
            'hook_slug' => '',
            'db_slug' => '',
            'plugin_slug' => '',
            'base_path' => '',
            'base_url' => '',
            'addon_listing_tab_id' => 'addons',
            'addon_listing_tab_slug' => 'addons-listing',
            'addon_listing_tab_name' => 'Addons',
            'file_headers' => array(),
            'show_category_count' => true,
        );
        
        public function __construct($options = array()){
            $this->active_addons = array();
            $this->user_options = $options;
            parent::__construct();
            
            $this->plugin_slug = $this->option('plugin_slug');
            $this->hook_slug = $this->option('hook_slug');
            $this->db_slug = $this->option('plugin_db_slug');
            
            if(vsp_is_request('admin')){
                add_action($this->hook_slug.'_settings_section',array($this,'set_settings_section'),10,100);
                add_action($this->hook_slug.'_settings_pages',array($this,'set_settings_page'),10,100);
                add_action($this->hook_slug.'_form_fields',array($this,'render_addons_page'),10,2);
            }
            
            if(vsp_is_request("ajax")){
                add_action($this->option("hook_slug")."_handle_addon_request",array($this,'handle_ajax_request'));
            }
            
        }
        
        public function handle_ajax_params($request = '',$msg){
            if(isset($_REQUEST[$request])){
                return $_REQUEST[$request];
            }
            
            wp_send_json_error(array('msg' => $msg));
        }
        
        public function handle_ajax_request(){
            if(isset($_REQUEST['addon_action'])){
                $action = $this->handle_ajax_params("addon_action",__("Addon Action Not Provided"));
                $addon = urldecode($this->handle_ajax_params('addon_slug',__("No Addon Selected")));
                $pathid = $this->handle_ajax_params("addon_pathid",__("Unable To Process Your Request"));
                
                
                if(empty($addon)){
                    wp_send_json_error(array("msg" => __("Invalid Addon")));
                }
                
                if($action == 'activate'){
                    if(!$this->is_active($addon)){
                        $addon_data = $this->search_get_addon($addon,$pathid);
                        
                        
                        if($addon_data['requirement_fullfiled'] === true){
                            $slug = $this->activate_addon($addon);
                            
                            if($slug){
                                wp_send_json_success(array('msg' => __("Addon Activated")));
                            }
                            
                        } else {
                            wp_send_json_error(array('msg' => __("Addon's Requried Plugins Not Active / Installed")));
                        }
                        
                    } else {
                        wp_send_json_error(array('msg' => __("Addon Already Active")));
                    }
                }
                
                if($action == 'deactivate'){
                    if($this->is_active($addon)){
                        $slug = $this->deactivate_addon($addon);

                        if($slug){
                            wp_send_json_success(array('msg' => __("Addon De-Activated")));
                        }

                    } else {
                        wp_send_json_error(array('msg' => __("Addon Is Not Active")));
                    }
                }
            }
            
            exit;
        }
        
        public function get_active_addons(){
            if(empty($this->active_addons)){
                $this->active_addons = get_option($this->option("db_slug").'_active_addons',array());
            }
            
            $this->active_addons = is_array($this->active_addons) ? $this->active_addons : array();
            return $this->active_addons;
        }
        
        public function update_active_addons($addons){
            update_option($this->option("db_slug").'_active_addons',$addons);
            $this->active_addons = $addons;
            return $this->active_addons;
        }
        
        public function activate_addon($addons_slug = ''){
            $active_addons = $this->get_active_addons();
            if(!in_array($addon_slug,$active_addons)){
                $active_addons[] = $addons_slug;
                $this->update_active_addons($active_addons);
                return true;
            }
            return false;
        }
        
        public function deactivate_addon($addons_slug = ''){
            $active_addons = $this->get_active_addons();
            if(in_array($addons_slug,$active_addons)){
                $key = array_search($addons_slug, $active_addons);
                unset($active_addons[$key]);
                $this->update_active_addons($active_addons);
                return true;
            }
            return false;
        }
        
        public function is_active($slug){
            $addons = $this->get_active_addons();
            
            if(in_array($slug,$addons)){
                return $slug;
            }
            
            return false;
        }
    }
}
