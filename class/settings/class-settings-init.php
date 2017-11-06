<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_WP_Settings")){
    class VSP_WP_Settings extends VSP_Class_Handler{
        protected $default_options = array(
            'settings_page_slug' => '',
            'pageName' => '',
            'plugin_slug' => '',
            'hook_slug' => '',
            'db_slug' => '',
            'callback_validation' => false,
            'show_status_page' => true,
        );
        
        public $page_hook = '';
        public $settings;
        public $settings_page = array();
        public $settings_section = array();
        public $settings_fields = array();
        private $create_function;
        private $settings_key;
        private $settings_values;
        private $pageName;

        public function __construct($options = array()) {
            parent::__construct($options);

            $this->settings_fields = array();
            $this->create_function = array();
            $this->status_page = null;
            $this->db_options = array();
            $this->name = empty($this->option('pageName')) ? __("Boiler Plate Settings") : $this->option('pageName');
            $this->db_slug = vsp_fix_slug($this->option('db_slug'));
            $this->hook_slug = vsp_fix_slug($this->option('hook_slug'));
            $this->settings_page = apply_filters($this->hook_slug.'_settings_pages',array());
            $this->settings_section = apply_filters($this->hook_slug.'_settings_section',array());
            $this->set_options();
            
            if($this->option('callback_validation') === true){
                $this->create_callback_function();
            }
            
            if(($this->option('show_status_page') === true)&& (vsp_is_request("admin"))){
                $this->status_page = new VSP_Settings_Status_Page($this);
            }
            
            if(empty($this->option('settings_page_slug'))) {
                add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            }
            
            add_action('admin_enqueue_scripts',array($this,'add_scripts'),10);
            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }
        
        public function set_options(){
            $options = array();
            $new_section = array();
            foreach($this->settings_section as $_page_id => $sections){
                if(!is_array($sections)){continue;}
                foreach($sections as $key => $section){
                    $options_new = get_option($this->db_slug.'_'.$section['id']);
                    if(is_array($options_new)){
                        $options = array_merge($options,$options_new);
                    }
                    
                    $new_section[] = array_merge(array('_page' => $_page_id),$section);
                }
            }
            $this->db_options = $options;
            $this->settings_section = $new_section;
        }
        
        public function add_scripts(){
            if(vsp_current_screen() == $this->option('settings_page_slug')){
                wp_enqueue_script('vsp-select2');
                wp_enqueue_script('vsp-plugins');
                wp_enqueue_script('vsp-framework');

                wp_enqueue_style('vsp-select2');
                wp_enqueue_style('vsp-plugins-css');
                wp_enqueue_style('vsp-framework-css');
            }
        }

        public function admin_menu() {
            $key = add_submenu_page('woocommerce',$this->name, $this->name, 'manage_woocommerce', $this->option('plugin_slug').'-settings', array($this,'admin_page'));
            $this->set_option('settings_page_slug',$key);
            $this->page_hook = $key;
        }

        private function create_callback_function(){
            $sec = $this->settings_section;
            foreach($sec as $sk => $s){
                if(is_array($s)){
                    $c = count($s);
                    $a = 0;
                    while($a < $c){
                        if(isset($s[$a]['validate_callback'])){
                            $this->create_function[] =  $s[$a]['id'];
                            $s[$a]['validate_callback'] = '';
                            $s[$a]['validate_callback'] = create_function('$fields', 'do_action("'.$this->hook_slug.'_settings_validate",$fields); do_action("'.$this->hook_slug.'_settings_validate_'.$s[$a]['id'].'",$fields);');
                        }
                        $a++;
                    }
                }
                $this->settings_section[$sk] = $s; 
            }
        } 

        public function admin_init(){
            $this->settings = new VSP_WP_Settings_Handler();
            $this->settings_fields = apply_filters($this->hook_slug.'_settings_fields',$this->settings_fields);
            $this->settings->add_pages($this->settings_page);
            
            foreach($this->settings_section as $section){
                $page = $section['_page'];
                unset($section['_page']);
                $this->settings->add_section($page,$section);
            }
            
            $fields = $this->settings_fields;
            
            foreach($fields as $page_id => $section_fields){
                foreach($section_fields as $section_id => $sfields){
                    if(is_array($sfields)){
                        foreach($sfields as $f){
                            $this->settings->add_field($page_id,$section_id,$f);
                        }
                    } else {
                        $this->settings->add_field($page_id,$section_id,$sfields);
                    }
                } 
            }
            
            $this->settings->init(array(),$this->get_common_args());
        }

        public function admin_page(){
            echo '<div class="wrap '.$this->hook_slug.'_settings '.$this->db_slug.'_settings vps_settings_page">  ';
            settings_errors();
            $this->settings->render_header();
            $this->settings->render_form();
            echo '</div>';
        }
    }
}