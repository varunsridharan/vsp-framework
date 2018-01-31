<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 03-01-2018
 * Time: 04:26 PM
 */

interface VSP_Plugin_Settings_Base {
    public function add_pages($pages = array());

    public function add_sections($sections = array());

    public function add_fields($fields = array());
}

abstract class VSP_Settings_Plugin implements VSP_Plugin_Settings_Base {
    public function __construct($hook_slug = '') {
        add_filter($hook_slug . "_settings_pages", array( &$this, 'add_pages' ));
        add_filter($hook_slug . "_settings_sections", array( &$this, 'add_sections' ));
        add_filter($hook_slug . "_settings_fields", array( &$this, 'add_fields' ));
    }
}