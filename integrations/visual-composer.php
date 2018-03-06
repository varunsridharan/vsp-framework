<?php
/**
 * Name:Visual Composer
 * Version:1.0
 */
define("VSP_VC_PATH", plugin_dir_path(__FILE__) . 'visual-composer/');

require_once( VSP_VC_PATH . 'functions.php' );

add_action('vsp_load_VSP_VC_Elements_Loader', function() {
    require_once( VSP_VC_PATH . 'class-vc-elements-loader.php' );
}, 0);

add_action('vsp_load_VSP_VC_Element', function() {
    require_once( VSP_VC_PATH . 'class-vc-element.php' );
}, 0);

add_action('vsp_load_VSP_VC_Fields', function() {
    require_once( VSP_VC_PATH . 'class-vc-fields.php' );
}, 0);

