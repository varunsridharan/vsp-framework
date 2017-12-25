<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Addons_Admin")){
    class VSP_Addons_Admin extends VSP_Addons_Core{

        public function __construct(){
            parent::__construct();
            $this->addons_list = array();
            $this->settings_pagehook = '';
            //add_action("admin_init",array($this,'on_wp_admin_init'));
        }
        
        public function on_wp_admin_init(){
            $page_hook = $this->option("settings");
            $this->settings_pagehook =  $page_hook->page_hook;
            add_action("load-".$page_hook->page_hook,array($this,'load_addons'));
        }
        
        public function load_addons(){
            if(isset($_REQUEST['tab'])){
                if($_REQUEST['tab'] == $this->option("addon_listing_tab_slug")){
                    
                }
            }
        }
        
        
        
        public function set_settings_page($pages){
            $pages[$this->option("addon_listing_tab_name")] = array(
                'name' => $this->option("addon_listing_tab_name"),
                'title' => $this->option("addon_listing_tab_title"),
                'icon' => $this->option("addon_listing_tab_icon"),
                'callback_hook' => 'vsp_render_'.$this->hook_slug.'_addons_list',
            );
            return $pages;
        }
        
        public function render_addons_page(){
            $this->addons_list = $this->search_get_addons();
            vsp_load_script('vsp-addons');
            vsp_load_style("vsp-addons");
            wp_enqueue_script( 'plugin-install' );

            wp_localize_script('vsp-addons','vsp_addons_settings', array(
                'hook_slug' => $this->option("hook_slug"),
                'save_slug' => $this->option("db_slug"),
            ));
            
            echo $this->render_category_html().$this->render_addons_html();
        }
        
        
        public function render_category_html(){
            if(!is_array($this->addon_cats)){
                return;
            }
            
            $html = '<div class="wp-filter">';
            $html .= $this->render_category_ulli_html();
            $html .= '<div class="vsp-addons-search-form '.$this->option("plugin_slug").'-addons-search-form">';
            $html .= '<input type="search" placeholder="'.__("Search Plugins").'" class="wp-filter-search" value="" name="s" />';
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        }
        
        public function render_category_ulli_html(){
            if(!is_array($this->addon_cats)){
                return;
            }
            
            $filter_ul_class = 'filter-links vsp-addons-category-listing '.$this->option("plugin_slug").'-addons-category-listing';
            $html = '<ul class="'.$filter_ul_class.'">';
            foreach($this->addon_cats as $slug => $name){
                if($this->addons_cats_count[$slug] === 0){continue;}
                
                if($this->option("show_category_count") === true){
                    $name = $name.' ('.$this->addons_cats_count[$slug].')';                    
                }
                
                $template = '<li id="%1$s" data-category="%1$s" class="%1$s vsp-addon-category %3$s-addon-category" ><a href="javascript:void(0);">%2$s</a></li>';
                $html .= sprintf($template,$slug,$name,$this->option("plugin_slug"));
            }
            
            $html .= '</ul>';
            return $html;
        }
        
        public function render_addons_html(){
            add_thickbox();
            $html = '' ;
            $html .= '<div class="wp-list-table widefat plugin-install">';
            $html .= '<div class="the-list vsp_addon_listing">';
            $html .= $this->render_single_addon();
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        }
        
        public function render_single_addon(){
            if(!is_array($this->addons_list)){
                return '';
            }
            
            
            $html = '';
            foreach($this->addons_list as $file => $data){
                $file = $data['addon_file_slug'];
                $_rplugins = $this->render_required_plugins_html($data);
                $cat_class = implode(" addon-",$data['category-slug']);
                $addon_slug = $data['addon_slug'];
                if(!empty($cat_class)){
                    $cat_class = 'addon-'.$cat_class;
                }
                
                $wrapperClass = 'plugin-card plugin-card-'.$addon_slug.' vsp-single-addon '.$cat_class;

                if($this->is_active($file) !== false){
                    $wrapperClass .= ' addon-active';
                } else {
                    $wrapperClass .= ' addon-inactive';
                }
                
                $addon_slug = $addon_slug.time();
                $pathid = md5($data['addon_path']);
                $html .= '<div id="'.$addon_slug.'" class="'.$wrapperClass.'" data-pathid="'.$pathid.'">';
                    $html .= '<div class="plugin-card-top">';
                        $html .= '<div class="name column-name">';
                            $html .= '<h3>'.$data['Name'].' [<small>V '.$data['Version'].'</small>] <img src="'.$data['icon'].'" class="plugin-icon addon-icon"/></h3>';
                        $html .= '</div>';
                
                        $html .= '<div class="desc column-description">';
                            $html .= '<p>'.$data['Description'].'</p>';
                            
                
                            $url = '<a href="' . admin_url( 'plugin-install.php?&isvspaddon=true&tab=plugin-information&plugin='. $file .'&pathid='.$pathid.'&TB_iframe=true&width=600&height=800' ) . '" class="thickbox open-plugin-details-modal" title="' . esc_attr( __( 'View WP All Import Pro Changelog' ) ) . '">' . __( 'View details' ) . '</a>';
                            $html .= '<p>'.$url.'</p>';
                        $html .= '</div>';
                    $html .= '</div>';
                
                if(!empty($_rplugins)){
                    $html .= '<div class="plugin-card-top vsp-addons-required-plugins">';
                    $html .= '<h3>'.__("Required Plugins").'</h3>';
                    $html .= $_rplugins;
                    $html .= '<p> <span>'.__("Above Mentioned Plugin name with version are Tested Upto").'</span> </p>';
                    $html .= '</div>';
                }
                
                $html .= '<div class="plugin-card-bottom">';
                    $html .= '<div class="column-updated">';
                    $html .= $this->get_addon_action_button($file,$addon_slug);
                    $html .= '</div>';
                    $html .= '<div class="column-downloaded vsp_addon_ajax_response"></div>';
                $html .= '</div>';

                $html .= '</div>';
            }
            return $html;
        }
        
        
        public function get_addon_action_button($file,$addon_slug){
            $slug = urlencode($file);
            $active_button = '<button type="button" data-outline="'.$addon_slug.'" data-filename="'.$file.'" class="vsp-active-addon button button-primary">%s</button>';
            $deactive_button = '<button type="button" data-outline="'.$addon_slug.'" data-filename="'.$file.'" class="vsp-deactive-addon button button-secondary">%s</button>';
            $active_button = sprintf($active_button,__("Activate"));
            $deactive_button = sprintf($deactive_button,__("De Activate"));
            return $active_button.$deactive_button;
        }
        
        private function render_required_plugins_html($data){
            if(!isset($data['required_plugins'])){
                return '';
            }
            
            if(!is_array($data['required_plugins']) && empty($data['required_plugins'])){
                return '';
            }
            
            $return_html = '<ul class="required_plugins">';
            foreach($data['required_plugins']['plugins'] as $slug => $info){
                $label = $this->get_plugin_status_label($info['status']);
                $li_class = 'required-'.$info['status'].' addon-status-inactive';
                $return_html .= '<li class="'.$li_class.'">';
                $return_html .= '<a href="'.$info['url'].'">'.$info['name'].'</a> ['.$info['required_version'].'] - '.$label;
                $return_html .= '</li>';
            }
            
            $return_html .= '</ul>';
            return $return_html;
        }
    }
}