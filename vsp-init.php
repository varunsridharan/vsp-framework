<?php
if( ! class_exists('VSP_Framework_Loader') ) {
    final class VSP_Framework_Loader {
        public static $_instance = NULL;
        public static $_loaded   = NULL;
        public static $data      = array();
        public static $meta_data = array();
        public static $callbacks = array();

        public function __construct() {
            self::$meta_data = array( 'lib' => array(), 'integrations' => array() );
            add_action("plugins_loaded", array( &$this, 'load_framework' ), 0);
            add_action('vsp_framework_load_lib_integrations', array( &$this, 'load_libs_integrations' ), 0);
            add_action('vsp_framework_loaded', array( &$this, 'load_plugins' ));
            if( is_admin() ) {
                add_filter('vsp_framework_syspage_framework_info', array( &$this, 'add_extra_info' ));
            }
        }

        public function add_extra_info($meta) {
            $integrations                     = VSP_Autoloader::get_integrations();
            $libs                             = VSP_Autoloader::get_libs();
            $meta[__("Loaded Library")]       = self::$meta_data['lib'];
            $meta[__("Loaded Integration")]   = self::$meta_data['integrations'];
            $meta[__("Bundled Integrations")] = array();
            $meta[__("Bundled Libs")]         = array();
            foreach( $integrations as $k => $v ) {
                $data = get_file_data(VSP_Autoloader::integration_path() . $v, array(
                    'Name'    => 'Name',
                    'Version' => 'Version',
                ), 'vsp');

                if( count(array_filter($data)) == 2 ) {
                    $meta[__("Bundled Integrations")][] = $data['Name'] . ' - ' . $data['Version'] . ' - ' . $v;
                } else {
                    $meta[__("Bundled Integrations")][] = $k . ' - ' . $v;
                }
            }
            foreach( $libs as $k => $v ) {
                $data = get_file_data(VSP_Autoloader::lib_path() . $v, array(
                    'Name'    => 'Name',
                    'Version' => 'Version',
                ), 'vsp');

                if( count(array_filter($data)) == 2 ) {
                    $meta[__("Bundled Libs")][] = $data['Name'] . ' - ' . $data['Version'] . ' - ' . $v;
                } else {
                    $meta[__("Bundled Libs")][] = $k . ' - ' . $v;
                }
            }
            return $meta;
        }

        public function load_framework() {
            $frameworks     = self::get();
            $latest_version = max(array_keys($frameworks));
            $info           = ( isset($frameworks[$latest_version]) ) ? $frameworks[$latest_version] : array();
            if( empty($info) ) {

                $msg = base64_encode(json_encode(self::$data));
                $ms  = __("Unable To Load VSP Framework. Please Contact The Author");
                $ms  .= '<p style="word-break: break-all;"> <strong>' . __("ERROR ID : ") . '</strong>' . $msg . '</p>';
                wp_die($ms);
            }
            self::$_loaded = $info;
            require_once( $info['framework_path'] . 'vsp-bootstrap.php' );
        }

        public static function instance() {
            if( self::$_instance === NULL ) {
                self::$_instance = new self;
            }
            return self::$_instance;
        }

        public function add($version, $data) {
            self::$data[$version] = $data;
            return $this;
        }

        public function manage_meta_data($data) {
            if( isset($data['lib']) && ! empty($data['lib']) ) {
                self::$meta_data['lib'] = array_merge(self::$meta_data['lib'], $data['lib']);
            }

            if( isset($data['integrations']) && ! empty($data['integrations']) ) {
                self::$meta_data['integrations'] = array_merge(self::$meta_data['integrations'], $data['integrations']);
            }

        }

        public function register_plugin($plugin_path = '', $meta_data = array(), $framework_path = '/vsp-framework/') {
            $plugin_path    = rtrim($plugin_path, '/');
            $framework_path = $plugin_path . $framework_path;

            if( file_exists($framework_path . 'vsp-bootstrap.php') ) {
                $info                   = get_file_data($framework_path . 'vsp-bootstrap.php', array(
                    'Name'       => 'Framework Name',
                    'Version'    => 'Version',
                    'TextDomain' => 'Text Domain',
                    'DomainPath' => 'Domain Path',
                ));
                $info['plugin_path']    = $plugin_path . '/';
                $info['framework_path'] = $framework_path;
                self::add($info['Version'], $info);
            }

            $this->manage_meta_data($meta_data);
            return $this;
        }

        public function get() {
            return self::$data;
        }

        public function loaded() {
            return self::$_loaded;
        }

        public function load_libs_integrations() {
            if( ! empty(self::$meta_data['lib']) ) {
                foreach( self::$meta_data['lib'] as $lib ) {
                    vsp_load_lib($lib);
                }
            }


            if( ! empty(self::$meta_data['integrations']) ) {
                foreach( self::$meta_data['integrations'] as $lib ) {
                    vsp_load_integration($lib);
                }
            }
        }

        public function register_callback($callback) {
            self::$callbacks[] = $callback;
            return $this;
        }

        public function load_plugins() {
            if( ! empty(self::$callbacks) ) {
                foreach( self::$callbacks as $callback ) {
                    call_user_func_array($callback, array());
                }
            }
        }
    }
}

if( ! function_exists('vsp_maybe_load') ) {
    function vsp_maybe_load($plugin_path = '', $meta_data = array(), $callback = array(), $framework_path = '/vsp-framework/') {
        VSP_Framework_Loader::instance()
                            ->register_plugin($plugin_path, $meta_data, $framework_path)
                            ->register_callback($callback);
    }
}

if( ! function_exists("vsp_maybe_framework_loader") ) {
    /**
     * Adds Passed Plugin path to the list array which later used to compare and
     * load the framework from a plugin which has the latest version of framework
     * @param $plugin_path
     * @deprecated This plugin has been deprecated instead use vsp_maybe_load
     */
    function vsp_mayby_framework_loader($plugin_path = '', $meta_data = array(), $callback = array(), $framework_path = '/vsp-framework/') {
        VSP_Framework_Loader::instance()
                            ->register_plugin($plugin_path, $meta_data, $framework_path)
                            ->register_callback($callback);
    }
}

