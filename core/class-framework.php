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
        use VSP_Framework_Trait;

        protected static $version         = NULL;
        protected        $default_options = array(
            'version'       => 1.0,
            'settings_page' => TRUE,
            'addons'        => TRUE,
            'plugin_file'   => __FILE__,
        );
        private          $settings        = NULL;
        private          $addons          = NULL;

        /**
         * VSP_Framework constructor.
         * @param array $options
         */
        public function __construct($options = array()) {
            parent::__construct($options);
            $this->parse_options();
            vsp_register_plugin($this->plugin_slug(), $this);

            $this->vsp_load_required_files();
            add_action("vsp_framework_init", array( $this, 'vsp_init_plugin' ));
        }

        /**
         * This function is called via hook
         * @hook vsp_framework_init
         */
        public function vsp_init_plugin() {
            $this->vsp_init_class();
            $this->vsp_register_hooks();
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

        private function vsp_register_hooks() {
            $this->register_hooks_before();
            add_action("init", array( $this, 'on_wp_init' ), 20);
            add_filter('load_textdomain_mofile', array( $this, 'load_textdomain' ), 10, 2);
            add_action('wp_enqueue_scripts', array( $this, 'frontend_assets' ));
            $this->register_hooks();
        }

        private function vsp_addon_init() {
            if( $this->option("addons") !== FALSE ) {
                $this->addons_init_before();
                $this->action("addons_init_before");
                $args         = $this->parse_args($this->option("addons"), $this->get_common_args(array( 'settings' => &$this->settings )));
                $this->addons = new VSP_Addons($args);
                $this->addons_init();
                $this->action("addons_init");
            }
        }

        private function vsp_settings_init() {
            if( $this->option("settings_page") !== FALSE ) {
                $this->settings_init_before();
                $this->action("settings_init_before");
                $args           = $this->parse_args($this->option("settings_page"), $this->get_common_args());
                $this->settings = new VSP_Settings_WPSF($args);
                $this->settings_init();
                $this->action("settings_init");
            }
        }

        private function vsp_load_required_files() {
            if( vsp_is_ajax() ) {
                $this->ajax_required_files();
            }

            if( vsp_is_admin() ) {
                $this->admin_required_files();
            }

            if( vsp_is_cron() ) {
                $this->cron_required_files();
            }

            if( vsp_is_frontend() ) {
                $this->frontend_required_files();
            }

            $this->loaded();
            $this->action("loaded");
        }

        public function on_wp_init() {
            $this->wp_init();
        }

        /**
         * @param array $options
         */
        protected function parse_options() {
            $this->options['plugin_slug'] = vsp_fix_slug($this->options["plugin_slug"]);
            $this->options['db_slug']     = vsp_fix_slug($this->options["db_slug"]);
            $this->options['hook_slug']   = vsp_fix_slug($this->options["hook_slug"]);
        }

        /**
         * Get the plugin url.
         *
         * @return string
         */
        public function plugin_url() {
            return untrailingslashit(plugins_url('/', $this->option('plugin_file')));
        }

        /**
         * Get the plugin path.
         *
         * @return string
         */
        public function plugin_path() {
            return untrailingslashit(plugin_dir_path($this->option('plugin_file')));
        }

        /**
         * Get Ajax URL.
         *
         * @return string
         */
        public function ajax_url() {
            return admin_url('admin-ajax.php', 'relative');
        }
    }
}