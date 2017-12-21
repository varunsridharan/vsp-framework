<?php
if(!defined("ABSPATH")){ exit; }
/**
 * General Constants
 */
vsp_define("VSP_V",vsp_version());
vsp_define("VSP_PATH",plugin_dir_path(__FILE__));
vsp_define("VSP_URL",trailingslashit(plugins_url("",__FILE__)));

/**
 * Framework Paths
 */ 
vsp_define("VSP_JS_PATH",VSP_PATH.'assets/js/');
vsp_define("VSP_CSS_PATH",VSP_PATH.'assets/css/');
vsp_define("VSP_IMG_PATH",VSP_PATH.'assets/img/');
vsp_define("VSP_CLASS",VSP_PATH.'class/');
vsp_define("VSP_LIB",VSP_PATH.'libs/');
vsp_define("VSP_FUNCTIONS",VSP_PATH.'functions/');
vsp_define("VSP_HELPERS",VSP_PATH.'helper/');

/**
 * Framework Paths as urls
 */ 
vsp_define("VSP_JS_URL",VSP_URL.'assets/js/');
vsp_define("VSP_CSS_URL",VSP_URL.'assets/css/');
vsp_define("VSP_IMG_URL",VSP_URL.'assets/img/');