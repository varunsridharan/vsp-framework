<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists('VSP_Framework_Options')){
    
    class VSP_Framework_Options extends VSP_Class_Handler {
        
        protected $default_options = array(
            'version' => 1.0,
            'settings_page'  => true,
            'addons' => true,
            'plugin_file' => __FILE__,
        );
        
        protected function parse_options($options = array()){
            $options = $this->parse_args($options,$this->default_options);
            $options['plugin_slug'] = vsp_fix_slug($options["plugin_slug"]);
            $options['db_slug'] = vsp_fix_slug($options["db_slug"]);
            $options['hook_slug'] = vsp_fix_slug($options["hook_slug"]);
            $this->options = $options;
        }
        
        public function plugin_url($file = __FILE__){
        }
        
    }
}