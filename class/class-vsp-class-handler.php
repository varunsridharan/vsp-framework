<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("VSP_Class_Handler") ) {
    /**
     * Class VSP_Class_Handler
     */
    abstract class VSP_Class_Handler {

        protected $options = array();

        protected $default_options = array();

        protected $user_options = array();

        protected $base_defaults = array(
            'plugin_slug' => '',
            'db_slug'     => '',
            'plugin_name' => '',
            'hook_slug'   => '',
        );

        public function __clone() {
            _doing_it_wrong(__FUNCTION__, __('Cloning instances of the class is forbidden.', 'vsp-framework'), $this->option("version"));
        }

        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __('Unserializing instances of the class is forbidden.', 'vsp-framework'), $this->option("version"));
        }

        /**
         * VSP_Class_Handler constructor.
         * @param array $options
         * @param array $defaults
         */
        public function __construct($options = array(), $defaults = array()) {
            if( empty($defaults) ) {
                $defaults = $this->default_options;
            }

            $defaults = $this->parse_args($defaults, $this->base_defaults);

            if( empty($options) ) {
                $options = $this->user_options;
            }

            $this->options = $this->parse_args($options, $defaults);
        }

        /**
         * @param array $new
         * @param       $defaults
         * @return array
         */
        protected function parse_args($new = array(), $defaults) {
            if( ! is_array($new) ) {
                $new = array();
            }
            return wp_parse_args($new, $defaults);
        }

        /**
         * @return bool|mixed
         */
        public function hook_slug() {
            return ( empty($this->option('hook_slug')) ) ? $this->db_slug() : $this->option('hook_slug');
        }

        /**
         * @return bool|mixed
         */
        public function db_slug() {
            return ( empty($this->option('db_slug')) ) ? $this->plugin_slug() : $this->option('db_slug');
        }

        /**
         * @return bool|mixed
         */
        public function version() {
            return $this->option('version');
        }

        /**
         * @return bool|mixed
         */
        public function plugin_slug() {
            return $this->option('plugin_slug');
        }

        /**
         * @return bool|mixed
         */
        public function plugin_name() {
            return $this->option("plugin_name");
        }

        /**
         * @param string $key
         * @param bool   $default
         * @return bool|mixed
         */
        protected function option($key = '', $default = FALSE) {
            return ( isset($this->options[$key]) ) ? $this->options[$key] : $default;
        }

        /**
         * @param $key
         * @param $value
         */
        protected function set_option($key, $value) {
            $this->options[$key] = $value;
        }

        /**
         * @param $array
         */
        protected function update_option($array) {
            $this->options = $array;
        }

        /**
         * @param array $extra_options
         * @return array
         */
        public function get_common_args($extra_options = array()) {
            $defaults = array(
                'plugin_slug' => $this->plugin_slug(),
                'db_slug'     => $this->db_slug(),
                'hook_slug'   => $this->hook_slug(),
                'plugin_name' => $this->option("plugin_name"),
            );

            return $this->parse_args($extra_options, $defaults);
        }

        /**
         * @param        $method
         * @param array  $args
         * @param string $class
         */
        protected function hook_function($method, $args = array(), $class = '') {
            if( empty($class) ) {
                $class = $this;
            }

            if( method_exists($class, $method) ) {
                call_user_func_array(array( $class, $method ), $args);
            }
        }

        /**
         * @param string $hook
         */
        protected function hook_function_action($hook = '') {
            $data = func_get_args();
            unset($data[0]);
            $this->hook_function($hook, $data);
            $this->hook_function('action', func_get_args());
        }

        /**
         * @param string $type
         * @param array  $args
         * @return mixed
         */
        private function action_filter($type = '', $args = array()) {
            $args[0] = $this->hook_slug() . '_' . $args[0];
            return call_user_func_array($type, $args);
        }

        /**
         * @return mixed
         */
        protected function filter() {
            return $this->action_filter('apply_filters', func_get_args());
        }

        /**
         * @return mixed
         */
        protected function action() {
            return $this->action_filter('do_action', func_get_args());
        }

        /**
         * @param $status
         * @return bool|string
         */
        protected function cache_output($status) {
            if( $status == 'start' ) {
                ob_start();
                return true;
            }

            $data = ob_get_clean();
            ob_flush();
            return $data;
        }

        /**
         * @param        $data
         * @param string $variable
         */
        protected function output($data, $variable = 'output_data') {
            if( ! isset($this->{$variable}) ) {
                $this->{$variable} = '';
            }

            $this->{$variable} .= $data;
        }

        /**
         * @param string $variable
         * @param bool   $_return
         * @param bool   $clear
         * @return bool|null
         */
        protected function _echo_output($variable = 'output_data', $_return = FALSE, $clear = TRUE) {
            $return = NULL;
            if( isset($this->{$variable}) ) {
                if( $_return === FALSE ) {
                    echo $this->{$variable};
                } else {
                    $return = $this->{$variable};
                }

                if( $clear ) {
                    $this->{$variable} = '';
                }
            }

            return $return;
        }
    }
}