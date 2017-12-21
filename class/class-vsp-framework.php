<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists('VSP_Framework')){
    
    class VSP_Framework extends VSP_Framework_Admin {
        private static $_instance = null;
        
        protected static $version = null;

        public static function instance(){
            if(null == self::$_instance){
                self::$_instance = new self;
            }
            return self::$_instance;
        }
        
        public function __construct($options = array()){
            $this->settings = null;
            $this->addons = null;
            parent::__construct($options);
            $this->parse_options($options);
            vsp_register_plugin($this->plugin_slug(),$this);
            $this->load_required_files();
            add_action("vsp_framework_init",array($this,'init_plugin'));
        }
        
        public function init_plugin(){
            $this->init_class();
            $this->init_hooks();
        }

        public function init_class(){
            $this->hook_function("hook_init_class",array('type' => 'before'));
            $this->action("init_before");
            $this->addon_init();
            
            if(vsp_is_request("admin")){
                $this->settings_init();
            }
            
            $this->action("init");
            $this->hook_function("hook_init_class",array('type' => 'after'));
        }
        
        public function init_hooks(){
            $this->hook_function("hook_init_hooks",array('type' => 'before'));
            add_action("init",array($this,'on_wp_init'));
            add_action('vsp_framework_init', array( $this, 'plugins_loaded' ));
            add_filter('load_textdomain_mofile',  array( $this, 'load_textdomain' ), 10, 2);
            add_action( 'wp_enqueue_scripts', array($this,'enqueue_assets') );
            $this->hook_function("hook_init_hooks",array('type' => 'after'));
        }
        
        public function load_required_files(){
            $this->hook_function("hook_load_required_files",array('type' => 'before'));
        }
        
        public function on_wp_init(){
            $this->hook_function("hook_on_wp_init",array('type' => 'before'));
        }

        public function plugins_loaded(){
            $this->action("loaded");
            $this->hook_function("hook_plugins_loaded");
        }

        public function load_textdomain($file = '',$domain = ''){
            return $file;
        }
        
        public function enqueue_assets(){
            
        }
    }
}