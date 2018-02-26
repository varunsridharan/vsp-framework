<?php
if( ! class_exists('VSP_Framework_Loader') ) {
    class VSP_Framework_Loader {
        public static $_instance = NULL;
        public static $_loaded   = NULL;
        public static $data      = array();

        public function __construct() {
            add_action("plugins_loaded", array( &$this, 'load_framework' ),0);
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

        public function register_plugin($plugin_path = '', $framework_path = '/vsp-framework/') {
            $plugin_path    = rtrim($plugin_path, '/');
            $framework_path = $plugin_path . $framework_path;

            if( file_exists($framework_path . 'vsp-bootstrap.php') ) {
                $default_headers        = array(
                    'Name'       => 'Framework Name',
                    'Version'    => 'Version',
                    'TextDomain' => 'Text Domain',
                    'DomainPath' => 'Domain Path',
                );
                $info                   = get_file_data($framework_path . 'vsp-bootstrap.php', $default_headers);
                $info['plugin_path']    = $plugin_path . '/';
                $info['framework_path'] = $framework_path;
                self::add($info['Version'], $info);
            }
        }

        public function get() {
            return self::$data;
        }
    }


}


global $vsp_plugins, $vsp_loaded_framework;
$vsp_plugins = $vsp_loaded_framework = array();


if( ! function_exists("vsp_mayby_framework_loader") ) {
    /**
     * Adds Passed Plugin path to the list array which later used to compare and
     * load the framework from a plugin which has the latest version of framework
     * @param $plugin_path
     */
    function vsp_mayby_framework_loader($plugin_path = '', $framework_path = '/vsp-framework/') {
        VSP_Framework_Loader::instance()
                            ->register_plugin($plugin_path, $framework_path);
    }
}

