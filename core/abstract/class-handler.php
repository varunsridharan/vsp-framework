<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("VSP_Class_Handler") ) {
    /**
     * Class VSP_Class_Handler
     */
    abstract class VSP_Class_Handler {
        private static $_instances      = array();
        protected      $instances       = array();
        public         $text_domain     = NULL;
        public         $version         = NULL;
        public         $file            = NULL;
        public         $slug            = NULL;
        public         $db_slug         = NULL;
        public         $name            = NULL;
        public         $hook_slug       = NULL;
        protected      $options         = array();
        protected      $default_options = array();
        protected      $user_options    = array();
        protected      $base_defaults   = array(
            'version'   => '',
            'file'      => '',
            'slug'      => '',
            'db_slug'   => '',
            'hook_slug' => '',
            'name'      => '',
        );

        public function __clone() {
            _doing_it_wrong(__FUNCTION__, __('Cloning instances of the class is forbidden.', 'vsp-framework'), $this->option("version"));
        }

        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __('Unserializing instances of the class is forbidden.', 'vsp-framework'), $this->option("version"));
        }

        public static function instance() {
            if( ! isset(self::$_instances[static::class]) ) {
                self::$_instances[static::class] = new static();
            }
            return self::$_instances[static::class];
        }

        public function __set_core($key = '', $default = '') {
            if( empty($this->$key) || is_null($this->$key) ) {
                $this->$key = $default;
            }
        }

        public function set_args($options = array(), $defaults = array()) {
            $defaults = empty($defaults) ? $this->default_options : $defaults;
            $defaults = $this->parse_args($defaults, $this->base_defaults);
            $options  = empty($options) ? $this->user_options : $options;
            $options  = $this->parse_args($options, $defaults);
            $this->__set_core('version', $options['version']);
            $this->__set_core('file', $options['file']);
            $this->__set_core('slug', $options['slug']);
            $this->__set_core('db_slug', $options['db_slug']);
            $this->__set_core('hook_slug', $options['hook_slug']);
            $this->__set_core('name', $options['name']);
            $this->options = $options;
        }

        protected function get_instance($key) {
            return ( isset($this->instances[$key]) ) ? $this->instances[$key] : FALSE;
        }

        protected function get_all_instances() {
            return $this->instances;
        }

        protected function set_instance($key, $instance) {
            $this->instances[$key] = $instance;
        }


        public function _instance($class, $force_instance = FALSE, $extra_option = array()) {
            if( $this->get_instance($class) === FALSE ) {
                if( $force_instance === TRUE && method_exists($class, 'instance') ) {
                    $this->set_instance($class, $class::instance());
                } else {
                    $instances = new $class($this->get_common_args($extra_option));
                    $this->set_instance($class, $instances);
                }

                if( $force_instance === TRUE ) {
                    $this->get_instance($class)
                         ->set_args($this->get_common_args($extra_option));
                }

            }
            return $this->get_instance($class);
        }

        /**
         *
         * @param array $options
         * @param array $defaults
         */
        public function __construct($options = array(), $defaults = array()) {
            $this->set_args($options, $defaults);
        }

        /**
         * @param array $new
         * @param       $defaults
         *
         * @return array
         */
        protected function parse_args($new = array(), $defaults) {
            if( ! is_array($new) ) {
                $new = array();
            }
            return wp_parse_args($new, $defaults);
        }

        public function file() {
            return $this->file;
        }

        /**
         * @return bool|mixed
         */
        public function version() {
            return $this->version;
        }


        public function slug($type = 'slug') {
            switch( $type ) {
                case 'slug':
                    return $this->slug;
                break;
                case 'db':
                    return $this->db_slug;
                break;
                case 'hook':
                    return $this->hook_slug;
                break;
            }
            return FALSE;
        }

        /**
         * @return bool|mixed
         */
        public function plugin_name() {
            return $this->name;
        }

        /**
         * @param string $key
         * @param bool   $default
         *
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
         *
         * @return array
         */
        public function get_common_args($extra_options = array()) {
            return $this->parse_args($extra_options, array(
                'plugin_slug' => $this->slug(),
                'db_slug'     => $this->slug('db'),
                'hook_slug'   => $this->slug('hook'),
                'plugin_name' => $this->plugin_name(),
            ));
        }

        /**
         * @param string $type
         * @param array  $args
         *
         * @return mixed
         */
        private function action_filter($type = '', $args = array()) {
            $args[0] = $this->slug('hook') . $args[0];
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
    }
}