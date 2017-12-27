<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Class_Handler")){
    abstract class VSP_Class_Handler {
        
        protected $options = array();

        protected $default_options = array();
        
        protected $user_options = array();
        
        protected $base_defaults = array(
            'page_hook' => '',
            'plugin_slug' => '',
            'db_slug' => '',
            'plugin_name' => '',
            'hook_slug' => '',
        );
        
        public function __clone() {
            _doing_it_wrong( __FUNCTION__, __( 'Cloning instances of the class is forbidden.'), $this->option("version"));
        }

        public function __wakeup() {
            _doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of the class is forbidden.'),$this->option("version"));
        }
        
        public function __construct($options = array(),$defaults = array()){
            if(empty($defaults)){
                $defaults = $this->default_options;
            }
            
            $defaults = $this->parse_args($defaults,$this->base_defaults);
            
            if(empty($options)){
                $options = $this->user_options;
            }
            
            $this->options = $this->parse_args($options,$defaults);
        }

        protected function parse_args($new = array(),$defaults){
            if(!is_array($new)){
                $new = array();
            }            
            return wp_parse_args($new,$defaults);
        }
        
        public function hook_slug(){
            return (empty($this->option('hook_slug'))) ? $this->db_slug() : $this->option('hook_slug');
        }
        
        public function db_slug(){
            return (empty($this->option('db_slug'))) ? $this->plugin_slug() : $this->option('db_slug');
        }

        public function version(){
            return $this->option('version');
        }

        public function plugin_slug(){
            return $this->option('plugin_slug');
        }
        
        public function plugin_name(){
            return $this->option("plugin_name");
        }
        
        protected function option($key = '',$default = false){
            return (isset($this->options[$key])) ? $this->options[$key] : $default;
        }
        
        protected function set_option($key,$value){
            $this->options[$key] = $value;
        }
        
        protected function update_option($array){
            $this->options = $array;
        }        
        
        public function get_common_args($extra_options = array()){
            $defaults = array(
                'plugin_slug' => $this->plugin_slug(),
                'db_slug' => $this->db_slug(),
                'hook_slug' => $this->hook_slug(),
                'plugin_name' => $this->option("plugin_name"),
            );
            
            return $this->parse_args($extra_options,$defaults);
        }
        
        protected function hook_function($method,$args = array(),$class = ''){
            if(empty($class)){
                $class = $this;
            }
            
            if(method_exists($class,$method)){
                call_user_func_array(array($class,$method),$args);
            }
        }
        
        protected function hook_function_action($hook = ''){
            $data = func_get_args();
            unset($data[0]);
            $this->hook_function($hook,$data);
            $this->hook_function('action',func_get_args());
        }
        
        private function action_filter($type = '',$args = array()){
            $args[0] = $this->hook_slug().'_'.$args[0];            
            php_logger($args);
            return call_user_func_array($type,$args);
        }
        
        protected function filter(){
            return $this->action_filter('apply_filters',func_get_args());
        }
        
        protected function action(){
            return $this->action_filter('do_action',func_get_args());
        }
        
        protected function cache_output($status){
            if($status == 'start'){
                ob_start();
                return;
            }
            
            $data = ob_get_clean();
            ob_flush();
            return $data;
        }
        
        protected function output($data,$variable='output_data'){
            if(!isset($this->{$variable})){
                $this->{$variable} = '';
            }
            
            $this->{$variable} .= $data;
        }
        
        protected function _echo_output($variable='output_data',$return = false,$clear = true){
            $return = null;
            if(isset($this->{$variable})){
                if($return === false){
                    echo $this->{$variable};
                } else {
                    $return = $this->{$variable};
                }

                if($clear){
                    $this->{$variable} = '';
                }
            }
            
            return $return;
        }
    }
}