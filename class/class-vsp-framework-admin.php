<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists('VSP_Framework_Admin') ) {

    class VSP_Framework_Admin extends VSP_Framework_Options {
        public function __construct($options = array()) {
            parent::__construct($options);
            if( vsp_is_admin() ) {
                add_action("vsp_framework_init", array( $this, 'admin_init' ));
            }
        }

        public function admin_init() {
            $this->admin_loaded();
            add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 99);
            add_action('admin_init', array( $this, 'on_admin_init' ));
            add_filter('plugin_row_meta', array( $this, 'row_links' ), 10, 2);
            add_filter('plugin_action_links_' . $this->option('plugin_file'), array( $this, 'action_links' ), 10, 10);
        }

        public function row_links($plugin_meta, $plugin_file) {
            return $plugin_meta;
        }

        public function action_links($action, $file, $plugin_meta, $status) {
            return $action;
        }
    }
}