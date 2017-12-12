<?php
if(!class_exists("VSP_Framework_Init")){
    
    class VSP_Framework_Init extends VSP_Framework_Options {
    
        public function __construct($options = array()){
            $this->settings = null;
            $this->addons = null;
            parent::__construct($options);
        }
        
        protected function addon_init(){
            if($this->option("addons") !== false){
                $this->hook_function("hook_addons_init",array('type' => 'before'));
                $args = $this->parse_args($this->option("addons"),$this->get_common_args(array('settings' => &$this->settings )));
                $this->addons = new VSP_Addons($args);
                $this->hook_function("hook_addons_init",array('type' => 'after'));
            }
        }
    
        protected function settings_init(){
            if($this->option("settings_page") !== false){
                $this->hook_function("hook_settings_init",array('type' => 'before'));
                $args = $this->parse_args($this->option("settings_page"),$this->get_common_args());
                $this->settings = new VSP_Settings($args);
                $this->hook_function("hook_settings_init",array('type' => 'after'));
            }
        }
    }
}
