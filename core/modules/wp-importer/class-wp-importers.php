<?php
/**
 * Created by PhpStorm.
 * Project : product-hide
 * User: varun
 * Date: 20-03-2018
 * Time: 11:43 AM
 */
if( ! defined('VSP_PATH') ) {
    die;
}
if( ! class_exists('VSP_WP_Importers') ) {


    final class VSP_WP_Importers extends VSP_Class_Handler {
        protected static $_importers = array();

        public function __construct() {
            parent::__construct(array(), array());
        }

        public function add($slug = '', $name = '', $description = '', $file = '', $wpsf = FALSE) {
            if( ! isset(self::$_importers[$slug]) ) {
                register_importer($slug, $name, $description, array( __CLASS__, 'load_importer' ));
                self::$_importers[$slug] = array( 'file' => $file, 'wpsf' => $wpsf );
            }
        }

        public static function load_importer() {
            if( isset($_GET['import']) ) {
                $importer = $_GET['import'];
                if( isset(self::$_importers[$importer]) ) {
                    if( self::$_importers[$importer]['wpsf'] === TRUE ) {
                        vsp_load_lib('wpsf');
                        wpsf_assets()->register_assets();
                        wpsf_assets()->render_framework_style_scripts();
                    }
                    $class = include self::$_importers[$importer]['file'];
                    $class->dispatch();
                }
            }

        }


    }

    if( ! function_exists('vsp_add_importer') ) {
        function vsp_add_importer($slug, $title, $description, $file, $wpsf) {
            return VSP_WP_Importers::instance()
                                   ->add($slug, $title, $description, $file, $wpsf);
        }
    }
}