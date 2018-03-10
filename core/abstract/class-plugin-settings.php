<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 */

/**
 * Class VSP_Settings_Plugin
 */
abstract class VSP_Plugin_Settings implements VSP_Plugin_Settings_Interface {
    /**
     * VSP_Settings_Plugin constructor.
     * @param string $hook_slug
     */
    public function __construct($hook_slug = '') {
        add_filter($hook_slug . "settings_pages", array( &$this, 'add_pages' ));
        add_filter($hook_slug . "settings_sections", array( &$this, 'add_sections' ));
        add_filter($hook_slug . "settings_fields", array( &$this, 'add_fields' ));
    }
}