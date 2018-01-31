<?php
if( ! defined("ABSPATH") ) {
    exit;
}

class VSP_WPSF_Integration {
    private static $_instance = NULL;

    public function __construct() {
        $this->tax_fields = array();
        $this->metabox_fields = array();
        $this->shortcode_fields = array();
        $this->wc_metabox_fields = array();
        add_action('init', array( &$this, 'init_wpsf' ), 10);
    }

    public static function instance() {
        if( NULL == self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function init_wpsf() {
        $this->get_fields();

        if( is_array($this->tax_fields) && ! empty($this->tax_fields) ) {
            $this->tax_instance = new WPSFramework_Taxonomy($this->tax_fields);
        }

        if( is_array($this->metabox_fields) && ! empty($this->metabox_fields) ) {
            $this->metabox_instance = new WPSFramework_Metabox($this->metabox_fields);
        }

        if( is_array($this->wc_metabox_fields) && ! empty($this->wc_metabox_fields) ) {
            $this->wc_metabox_instance = new WPSFramework_WC_Metabox($this->wc_metabox_fields);
        }

        if( is_array($this->shortcode_fields) && ! empty($this->shortcode_fields) ) {
            $this->shortcode_fields['settings'] = array(
                'button_title' => __("VSP Shortcodes", 'vsp-framework'),
            );
            $this->shortcode_instance = new WPSFramework_Shortcode_Manager($this->shortcode_fields);
        }
    }

    public function get_fields() {
        $this->tax_fields = apply_filters("vsp_taxonomy_fields", $this->tax_fields);
        $this->metabox_fields = apply_filters("vsp_metabox_fields", $this->metabox_fields);
        $this->shortcode_fields = apply_filters("vsp_shortcode_fields", $this->shortcode_fields);
        $this->wc_metabox_fields = apply_filters('vsp_wc_metabox_fields', $this->wc_metabox_fields);
    }

}

return VSP_WPSF_Integration::instance();