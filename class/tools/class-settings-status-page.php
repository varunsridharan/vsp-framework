<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Settings_Status_Page")){
    
    class VSP_Settings_Status_Page extends VSP_Class_Handler {
         
        public function __construct(VSP_WP_Settings $instance,$options = array()){
            parent::__construct($options,array(
            'tab_id' => 'sys_status',
            'tab_slug' => 'sys-status',
            'tab_title' => __("System Status"),
        ));
            
            $instance->settings_page[] = array(
                'id' => $this->option('tab_id'),
                'slug' => $this->option('tab_slug'),
                'title' => $this->option('tab_title'),
            );
            
            add_filter($instance->hook_slug.'_form_fields',array($this,'list_addons'),10,2);
        }
        
        public function list_addons($return = '',$tab = ''){
            if(strtolower($tab) === $this->option('tab_id')){
                $m = new VSP_Site_Status_Report;
                $return = $m->get_output();
            }
            
            return $return;
        }
    }
}