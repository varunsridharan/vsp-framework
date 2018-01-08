<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists('VSP_Framework')){
    
    abstract class VSP_Framework extends VSP_Framework_Admin implements VSP_Framework_Interface{
        private static $_instance = null;
        
        protected static $version = null;

        public function __construct($options = array()){
            parent::__construct($options);
            $this->settings = null;
            $this->addons = null;
            $this->parse_options($options);
            vsp_register_plugin($this->plugin_slug(),$this);
            $this->vsp_load_required_files();
            $this->hook_function_action("loaded");
            add_action("vsp_framework_init",array($this,'vsp_init_plugin'));
        }

        public function vsp_init_plugin(){
            $this->vsp_init_class();
            $this->vsp_init_hooks();
        }

        private function vsp_init_class(){
            $this->init_before();
            $this->action("init_before");
            $this->vsp_addon_init();
            
            if(vsp_is_request("admin")){
                $this->vsp_settings_init();
            }
            $this->init();            
            $this->action("init");
        }
        
        private function vsp_init_hooks(){
            $this->init_hooks_before();
            add_action("init",array($this,'vsp_on_wp_init'));
            add_filter('load_textdomain_mofile',  array( $this, 'load_textdomain' ), 10, 2);
            add_action( 'wp_enqueue_scripts', array($this,'add_assets') );
            $this->init_hooks();
        }

        private function vsp_addon_init(){
            if($this->option("addons") !== false){
                $this->action("addons_init_before");
                $args = $this->parse_args($this->option("addons"),$this->get_common_args(array('settings' => &$this->settings )));
                $this->addons = new VSP_Addons($args);
                $this->action("addons_init");
            }
        }
    
        private function vsp_settings_init(){
            if($this->option("settings_page") !== false){
                $this->action("settings_init_before");
                $this->settings_init_before();
                $args = $this->parse_args($this->option("settings_page"),$this->get_common_args());
                $this->settings = new VSP_Settings_WPSF($args);
                $this->settings_init();
                $this->action("settings_init");
            }
        }

        private function vsp_load_required_files(){
            $this->hook_function("load_required_files");
        }

        public function vsp_on_wp_init(){
            $this->hook_function("on_wp_init");
        }

        public function load_textdomain($file = '',$domain = ''){
            return $file;
        }

        public function init_before(){}

        public function init_hooks_before(){}

        public function addons_init_before(){}
    }
}