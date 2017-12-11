<?php

if(!defined("ABSPATH")){exit;}

if(!class_exists("VSP_Settings_Metaboxes")){
    class VSP_Settings_Metaboxes extends VSP_Class_Handler {
        protected $default_options = array(
            'show_adds' => true,
            'show_faqs' => true,
        );
        
        public function __construct($options = array()){
            parent::__construct($options);
        }
        
        public function render_metaboxes(){
            ?>

            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <?php

                    $this->render_faqs();
                    $this->render_adds();
                            ?>
                </div>
            </div>
        <?php
        }

        public function handle_faqs($cache){
            $return = array();
            foreach($cache as $page => $sections){
                if(!isset($return[$page])){$return[$page] = array();}
                foreach($sections as $section => $faqs){
                    if(!isset($return[$page][$section])){$return[$page][$section] = array();}
                    foreach($faqs as $id => $faq){
                        $return[$page][$section][vsp_fix_title($faq['question'])] = $faq;
                    }
                }
            }
            return $return;
        }
        
        private function get_adds_data(){
            $cache = vsp_get_cache('vsp_shameless_plugs');
            if(false === $cache){
                $cache = vsp_get_cdn("shameless_plug.json",true);
                vsp_set_cache("vsp_shameless_plug",$cache,'10_days');
            }
            
            return $cache;
        }
        
        private function get_faq_datas(){
            $cache = vsp_get_cache($this->plugin_slug().'-faqs6');
            if(false === $cache){
                $url = $this->plugin_slug().'/faq.json';
                $cache = vsp_get_cdn($url,true);                
                $cache = $this->handle_faqs($cache);
                vsp_set_cache($this->plugin_slug().'-faqs',$cache,'10_days');
            }
            return $cache;            
        }
        
    
        
        public function render_faqs(){
            $faqs = $this->get_faq_datas();
            $current_tabs = $this->option('settings')->option("current_page");
            $page_id = $current_tabs['id'];
            $page_slug = $current_tabs['slug'];
            
            echo '<div class="postbox" id="vsp-settings-faq">';
            echo '<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle"><span>'.__("F A Q's").'</span></h2>';
            $current_faqs = isset($faqs[$page_id]) ? $faqs[$page_id] : array();
            if(empty($current_faqs)){
                $current_faqs = isset($faqs[$page_slug]) ? $faqs[$page_slug] : array();
            }
            
            $current_faqs = array('prefix_sec_id' => $this->db_slug(),'faqs' => $current_faqs);
            echo vsp_js_vars('vspFramework_Settings_Faqs',$current_faqs,true);
            echo '<div class="inside">';
            echo '</div>';
            echo '</div>';
        }
        
        public function render_adds(){
            if($this->option("show_adds") === false){
                return;
            }
            vsp_load_style('woothemes-flexslider');
            vsp_load_script('woothemes-flexslider');
            
            $adds_json = $this->get_adds_data();
             shuffle($adds_json);
            echo '<div class="postbox" id="vsp-adds-sidebar">';
                echo '<div class="inside"> ';
                    echo '<div class="owl-carousel owl-theme vsp-adds-slider"> ';
                        foreach($adds_json as $slug => $r){
                            include(VSP_PATH.'views/settings-add.php');
                        }
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }
    }
}
