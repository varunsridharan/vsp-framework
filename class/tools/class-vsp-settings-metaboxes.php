<?php

if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("VSP_Settings_Metaboxes") ) {
    /**
     * Class VSP_Settings_Metaboxes
     */
    class VSP_Settings_Metaboxes extends VSP_Class_Handler {

        protected $default_options = array(
            'show_adds' => TRUE,
            'show_faqs' => TRUE,
        );

        /**
         * VSP_Settings_Metaboxes constructor.
         * @param array $options
         */
        public function __construct($options = array()) {
            parent::__construct($options);
        }

        public function render_metaboxes() {
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

        /**
         * @param $cache
         * @return array
         */
        public function handle_faqs($cache) {
            $return = array();
            foreach( $cache as $page => $sections ) {
                if( ! isset($return[$page]) ) {
                    $return[$page] = array();
                }

                foreach( $sections as $sec_id => $section ) {
                    if( isset($section['question']) ) {
                        $return[$page][vsp_fix_title($section['question'])] = $section;
                    } else {
                        foreach( $section as $faq ) {
                            $return[$page][$sec_id][vsp_fix_title($faq['question'])] = $faq;
                        }
                    }
                }
            }

            return $return;
        }

        /**
         * @return array|bool|mixed|object|\WP_Error
         */
        private function get_adds_data() {
            $cache = vsp_get_cache('vsp_shameless_plug');
            if( FALSE === $cache ) {
                $cache = vsp_get_cdn("shameless_plug.json", TRUE);
                if( is_wp_error($cache) ) {
                    return FALSE;
                }

                if( empty($cache) ) {
                    return FALSE;
                }
                vsp_set_cache("vsp_shameless_plug", $cache, '10_days');
            }

            return $cache;
        }

        /**
         * @return array|bool|mixed|object|\WP_Error
         */
        private function get_faq_datas() {
            $cache = vsp_get_cache($this->plugin_slug() . '-faqs');
            if( FALSE === $cache ) {
                $url = $this->plugin_slug() . '/faq.json';
                $cache = vsp_get_cdn($url, TRUE);
                if( empty($cache) ) {
                    return FALSE;
                }

                if( is_wp_error($cache) ) {
                    return FALSE;
                }
                $cache = $this->handle_faqs($cache);
                vsp_set_cache($this->plugin_slug() . '-faqs', $cache, '10_days');
            }
            return $cache;
        }

        public function render_faqs() {
            if( $this->option("show_faqs") === FALSE ) {
                return;
            }
            $faqs = $this->get_faq_datas();
            if( empty($faqs) ) {
                return;
            }
            vsp_load_script("vsp-simscroll");
            echo '<div class="postbox" id="vsp-settings-faq">';
            echo '<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle"><span>' . __("F A Q's", 'vsp-framework') . '</span></h2>';
            $current_faqs = array( 'prefix_sec_id' => $this->db_slug(), 'faqs' => $faqs );
            echo vsp_js_vars('vspFramework_Settings_Faqs', $current_faqs, TRUE);
            echo '<div class="inside">';
            echo '</div>';
            echo '</div>';
        }

        public function render_adds() {
            if( $this->option("show_adds") === FALSE ) {
                return;
            }
            vsp_load_style('vsp-owlslider');
            vsp_load_script('vsp-owlslider');
            $adds_json = $this->get_adds_data();
            shuffle($adds_json);
            echo '<div class="postbox" id="vsp-adds-sidebar">';
            echo '<div class="inside"> ';
            echo '<div class="owl-carousel owl-theme vsp-adds-slider"> ';
            foreach( $adds_json as $slug => $r ) {
                include( VSP_PATH . 'views/settings-add.php' );
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
}
