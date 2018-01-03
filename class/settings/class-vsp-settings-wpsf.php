<?php
if(!class_exists("VSP_Settings_WPSF")){
    class VSP_Settings_WPSF extends VSP_Class_Handler {
        protected $default_options = array(
            'show_adds' => true,
            'show_faqs' => true,
            'plugin_slug' => '',
            'hook_slug' => '',
            'db_slug' => '',
            'status_page' => true,

            'menu_parent' => false,
            'menu_title' => false,
            'menu_type' => false,
            'menu_slug' => false,
            'menu_icon' => false,
            'menu_position' => false,
            'menu_capability' => false,
            'ajax_save' => false,
            'show_reset_all' => false,
            'framework_title' => false,
            'options_name' => false,
            'style' => 'modern',
            'is_single_page' => false,
            'is_sticky_header' => false,
            'extra_css' => array('vsp-plugins','vsp-framework'),
            'extra_js' => array('vsp-plugins','vsp-framework'),
        );
        
        private $final_options = array();
        
        public function __construct($options = array()){
            parent::__construct($options);
            
            if(vsp_is_request("admin") || vsp_is_request("ajax")){
                $this->pages = array();
                $this->fields = array();
                $this->sections = array();
                $this->status_page = null;
                $this->hook_slug = vsp_fix_slug($this->option('hook_slug'));
                
                $this->get_settings_config();
                $this->settings_pages();
                $this->settings_sections();
                $this->settings_fields();
                
                if($this->option("status_page")  !== false && vsp_is_request('admin') === true){
                    $this->update_status_page();
                }

                $this->final_array();
                add_action("init",array(&$this,'init_settings'),10);
               add_action("vsp_wp_settings_simple_footer",array(&$this,'render_settings_metaboxes'));
                add_action('vsp_show_sys_page',array(&$this,'render_sys_page'));
            }
        }
     
        public function settings_pages(){
            $this->pages = $this->filter("settings_pages",$this->pages);
        }
        
        public function settings_sections(){
            $this->sections = $this->filter("settings_sections",$this->sections);
        }
        
        public function settings_fields(){
            $this->fields = $this->filter('settings_fields',$this->fields);
        }

        public function get_settings_config(){
            
            $defaults = array('menu_parent','menu_title','menu_type','menu_slug','menu_icon','menu_position','menu_capability','ajax_save','show_reset_all','framework_title','options_name','style','is_single_page','is_sticky_header','extra_css','extra_js','buttons');
            $this->page_config = array();
            foreach($defaults as $op){
                $this->page_config[$op] = $this->option($op,'');
            }
            
            if(!isset($this->page_config['override_location']))
                $this->page_config['override_location'] = VSP_PATH.'/views/';
        }
        
        public function final_array(){
            $pages = $this->pages;
            foreach($this->sections as $i => $v){
                list($page,$section) = explode('/',$i);
                if(isset($pages[$page])){
                    if(!isset($pages[$page]['sections'])){
                        $pages[$page]['sections'] = array();
                    }
                    
                    $pages[$page]['sections'][$section] = $v;
                }
            }
            
            
            foreach($this->fields as $id => $fields){
                $page = $section = null;
                
                $page = explode('/',$id);
                if(isset($page[1])){
                    $section = $page[1];
                }
                
                $page = $page[0];
                
                if($section === null){
                    if(isset($pages[$page]) && !isset($pages['section'])){
                        if(!isset($pages[$page]['fields'])){
                            $pages[$page]['fields'] = array();
                        }
                        
                        $pages[$page]['fields'] = array_merge($pages[$page]['fields'],$fields);
                    }
                } else {
                    if(isset($pages[$page]) && !isset($pages['fields'])){
                        if(!isset($pages[$page]['sections'][$section]['fields'])){
                            $pages[$page]['sections'][$section]['fields'] = array();
                        }
                        
                        $pages[$page]['sections'][$section]['fields'] = array_merge($pages[$page]['sections'][$section]['fields'],$fields);
                    }
                }
                
            }
            $this->final_options = $pages;
        }
    
        public function init_settings(){
            $this->framework = new WPSFramework(array(
                'settings' => array(
                    'config' => $this->page_config,
                    'options' => $this->final_options,
                )
            ));
        }

        public function render_settings_metaboxes(){
            $adds = new VSP_Settings_Metaboxes($this->get_common_args(array(
                'show_adds' => $this->option('show_adds'),
                'show_faqs' => $this->option('show_faqs'),
            )));
            $adds->render_metaboxes();
        }
        
        private function update_status_page(){
            $defaults = array('name' => 'sys-page','title' => __("System Status",'vsp-framework'),'icon' => 'fa fa-info-circle');
            $status_page = $this->option('status_page');
            $status_page = ($status_page !== false && !is_array($status_page)) ? array() : $status_page;
            $status_page = $this->parse_args($status_page,$defaults);
            
            $this->pages[$status_page['name']] = array(
                'name' => $status_page['name'],
                'title' => $status_page['title'],
                'icon' => $status_page['icon'],
                'callback_hook' => 'vsp_show_sys_page',
                'fields' => array(),
            );
        }
        
        public function render_sys_page(){
            echo VSP_Site_Status_Report::instance()->get_output();
        }
    }
}