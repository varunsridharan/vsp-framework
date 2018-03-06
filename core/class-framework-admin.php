<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists('VSP_Framework_Admin') ) {
    /**
     * Class VSP_Framework_Admin
     */
    class VSP_Framework_Admin extends VSP_Class_Handler {
        protected $row_actions = array();

        protected $action_links = array();

        /**
         * VSP_Framework_Admin constructor.
         * @param array $options
         */
        public function __construct($options = array()) {
            parent::__construct($options);
            if( vsp_is_admin() ) {
                add_action("vsp_framework_init", array( $this, 'on_admin_init' ));
            }
        }

        public function on_admin_init() {
            $this->admin_loaded();
            add_action('admin_enqueue_scripts', array( $this, 'admin_assets' ), 99);
            add_action('admin_init', array( $this, 'wp_admin_init' ));
            add_filter('plugin_row_meta', array( $this, 'row_links' ), 10, 2);
            add_filter('plugin_action_links_' . $this->option('plugin_file'), array( $this, 'action_links' ), 10, 10);
        }

        /**
         * @param $plugin_meta
         * @param $plugin_file
         * @return mixed
         */
        public function row_links($plugin_meta, $plugin_file) {
            if( $this->option('plugin_file') === $plugin_file ) {
                if( is_array($this->row_actions) && ! empty($this->row_actions) ) {
                    $is_before = ( isset($this->row_actions['before']) ) ? TRUE : FALSE;
                    unset($this->row_actions['before']);
                    if( $is_before === TRUE ) {
                        $plugin_meta = array_merge($this->row_actions, $plugin_meta);
                    } else {
                        $plugin_meta = array_merge($plugin_meta, $this->row_actions);
                    }
                }
            }

            return $plugin_meta;
        }

        /**
         * @param $action
         * @param $plugin_file
         * @param $plugin_meta
         * @param $status
         * @return mixed
         */
        public function action_links($action, $plugin_file, $plugin_meta, $status) {
            if( $this->option('plugin_file') === $plugin_file ) {

                if( is_array($this->action_links) && ! empty($this->action_links) ) {
                    $is_before = ( isset($this->action_actions['before']) ) ? TRUE : FALSE;
                    unset($this->action_links['before']);
                    if( $is_before === TRUE ) {
                        $action = array_merge($this->action_links, $action);
                    } else {
                        $action = array_merge($action, $this->action_links);
                    }
                }


            }
            return $action;
        }
    }
}