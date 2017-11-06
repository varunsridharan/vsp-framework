<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Plugin_Hooks")){
    abstract class VSP_Plugin_Hooks extends VSP_Class_Handler {
        
        private function do_action($name = '',$before = false,$args = array()){
            $name = $this->get_hook_name($name,$before);
            do_action($name,$args);
            return true;
        }
        
        private function do_filter($name = '',$before = false,$args = array()){
            $name = $this->get_hook_name($name,$before);
            return apply_filters($name,$args);
        }
        
        private function get_hook_name($name,$before = false){
            if($before){
                return $this->hook_slug().'_'.$name.'_before';
            }
            return $this->hook_slug().'_'.$name;
        }
        
        public function plugin_loaded($before = false){
            $this->do_action('loaded',$before);
        }
        
        public function plugin_init($before = false){
            $this->do_action('init',$before);
        }
        
        public function addon_load($before = false){
            $this->do_action("addon_load",$before);
        }
    }
}