<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists('VSP_Framework') ) {
    /**
     * Class VSP_Framework
     * This class should be extened and used in a plugins class
     */
    abstract class VSP_Framework extends VSP_Framework_Admin implements VSP_Framework_Interface {

        protected static $version = NULL;

        protected $default_options = array(
            'version'       => 1.0,
            'settings_page' => TRUE,
            'addons'        => TRUE,
            'plugin_file'   => __FILE__,
        );

        /**
         * VSP_Framework constructor.
         * @param array $options
         */
        public function __construct($options = array()) {
            parent::__construct($options);
            $this->settings = NULL;
            $this->addons = NULL;
            $this->parse_options($options);
            vsp_register_plugin($this->plugin_slug(), $this);
            $this->vsp_load_required_files();
            $this->hook_function_action("loaded");
            add_action("vsp_framework_init", array( $this, 'vsp_init_plugin' ));
        }

        /**
         * This function is called via hook
         * @hook vsp_framework_init
         */
        public function vsp_init_plugin() {
            $this->vsp_init_class();
            $this->vsp_init_hooks();
        }

        private function vsp_init_class() {
            $this->init_before();
            $this->action("init_before");
            $this->vsp_addon_init();

            if( vsp_is_admin() ) {
                $this->vsp_settings_init();
            }
            $this->init();
            $this->action("init");
        }

        private function vsp_init_hooks() {
            $this->init_hooks_before();
            add_action("init", array( $this, 'vsp_on_wp_init' ));
            add_filter('load_textdomain_mofile', array( $this, 'load_textdomain' ), 10, 2);
            add_action('wp_enqueue_scripts', array( $this, 'add_assets' ));
            $this->init_hooks();
        }

        private function vsp_addon_init() {
            if( $this->option("addons") !== FALSE ) {
                $this->action("addons_init_before");
                $args = $this->parse_args($this->option("addons"), $this->get_common_args(array( 'settings' => &$this->settings )));
                $this->addons = new VSP_Addons($args);
                $this->action("addons_init");
            }
        }

        private function vsp_settings_init() {
            if( $this->option("settings_page") !== FALSE ) {
                $this->action("settings_init_before");
                $this->settings_init_before();
                $args = $this->parse_args($this->option("settings_page"), $this->get_common_args());
                $this->settings = new VSP_Settings_WPSF($args);
                $this->settings_init();
                $this->action("settings_init");
            }
        }

        private function vsp_load_required_files() {
            $this->hook_function("load_required_files");
        }

        public function vsp_on_wp_init() {
            $this->hook_function("on_wp_init");
        }

        /**
         * @param string $file
         * @param string $domain
         * @return string
         */
        public function load_textdomain($file = '', $domain = '') {
            return $file;
        }

        public function init_before() {
        }

        public function init_hooks_before() {
        }

        public function addons_init_before() {
        }

        /**
         * @param array $options
         */
        protected function parse_options($options = array()) {
            $options = $this->parse_args($options, $this->default_options);
            $options['plugin_slug'] = vsp_fix_slug($options["plugin_slug"]);
            $options['db_slug'] = vsp_fix_slug($options["db_slug"]);
            $options['hook_slug'] = vsp_fix_slug($options["hook_slug"]);
            $this->options = $options;
        }
    }
}