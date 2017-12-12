<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Settings")){
    class VSP_Settings extends VSP_Class_Handler{
        protected $default_options = array(
            'settings_page_slug' => '',
            'pageName' => '',
            'plugin_slug' => '',
            'hook_slug' => '',
            'db_slug' => '',
            'callback_validation' => false,
            'show_status_page' => true,
            'show_adds' => true,
            'assets' => array(),
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
            
            if(empty($this->option('settings_page_slug'))) {
                add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            }
            
            add_action('admin_enqueue_scripts',array($this,'add_scripts'),10);
            add_action('load-options.php', array( $this, 'admin_init' ) );
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

                $extra_assets = $this->option("assets");
                
                vsp_load_script('vsp-plugins');
                vsp_load_script('vsp-framework');

                vsp_load_style('vsp-plugins');
                vsp_load_style('vsp-framework');
                
                if(is_array($extra_assets)){
                    foreach($extra_assets as $ass){
                        vsp_load_style($ass);
                        vsp_load_script($ass);
                    }
                }
                
            }
        }

        public function admin_menu() {
            $key = add_submenu_page('woocommerce',$this->name, $this->name, 'manage_woocommerce', $this->option('plugin_slug').'-settings', array($this,'admin_page'));
            $this->set_option('settings_page_slug',$key);
            $this->page_hook = $key;
            add_action('load-'.$this->page_hook,array($this,'admin_init'));
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
            $this->settings_page = apply_filters($this->hook_slug.'_settings_pages',array());
            $this->settings_section = apply_filters($this->hook_slug.'_settings_section',array());
            $this->set_options();
            
            if($this->option('callback_validation') === true){
                $this->create_callback_function();
            }
            
            if(($this->option('show_status_page') === true)&& (vsp_is_request("admin"))){
                $this->status_page = new VSP_Settings_Status_Page($this);
            }
            
            vsp_settings_save_sections($this->plugin_slug(),$this->db_slug(),$this->settings_section);
            
            $this->settings = new VSP_Settings_Handler;
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
            $wrap_class = 'wrap ' . (($this->option("show_adds") === true) ? 'vsp_with_metaboxes' : '' );
            echo '<div class="'.$wrap_class.'">';
                echo '<div class="'.$this->hook_slug.'_settings '.$this->db_slug.'_settings vps_settings_page">';
                settings_errors();
                $this->settings->render_header();
                $forms = $this->settings->render_form();
            if(!empty($forms['sub_tabs'])){
                echo '<div id="poststuff">';
                        echo '<div class="metabox-holder columns-2" id="post-body">';
                            echo '<div id="post-body-content">';
                                echo '<div class="postbox"> ';
                                    echo '<h2 class="hndle ui-sortable-handle">'.$forms['sub_tabs'].'</h2>';
                                    echo '<div class="inside">'.$forms['form'].'</div>';
                                echo '</div>';
                            echo '</div>'; 

                            $adds = new VSP_Settings_Metaboxes(array_merge(array('settings' => &$this->settings),$this->get_common_args()));
                            $adds->render_metaboxes();
                        echo '</div>';
                    echo '</div>';
            } else {
                echo $forms['form'];
            }
                    
                echo '</div>';
            echo '</div>';
        }
        
        public function add_generator(){
            if($this->option("show_adds") !== true){
                return;
            }
            
            $resource = wp_remote_get("http://localhost/add.php");
            if(is_wp_error($resource)){
                
            } else {
                $resource = wp_remote_retrieve_body($resource);
                $resource = json_decode($resource,true);
            }
            
            
            echo '<div class="vsp-add-container">';
            
            echo '<div class="postbox"> <div class="inside"> ';
            echo '<div class="vsp-adds-slider"> ';
            foreach($resource as $slug => $r){
                include(VSP_PATH.'views/settings-add.php');
            }
            echo '</div>';
            
            echo '</div></div>';
            echo '</div>';
        }
    }
}