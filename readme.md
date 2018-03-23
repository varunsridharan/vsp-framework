### Framework Used
- WPSF - http://github.com/wpsf/


# WPSF Framework (Used)
https://github.com/wpsf/

# PHP Args

```php

<?php

$plugin_settings = array(
    'version' => '1.0',
    'plugin_file' => __FILE__,
    'settings_page_slug' => '',
    'plugin_slug' => '',
    'db_slug' => '',
    'plugin_name' => '',
    'hook_slug' => '',
    
    'settings_page' => array(
        'menu_parent' => false,
        'menu_title' => false,
        'menu_type' => false,
        'menu_slug' => false,
        'menu_icon' => false,
        'menu_position' => false,
        'menu_capability' => false,
        'ajax_save' => false,
        'show_reset_all' => false,
        'framework_title' => false,
        'options_name' => false,
        'style' => 'modern',
        'is_single_page' => false,
        'is_sticky_header' => false,
        
        'status_page' => array(
            'name' => '',
            'title' => '',
            'icon' => '',
        ),

        'show_adds' => true,
        'show_faqs' => true,
    ),
    
    'addons' => array(
        'addon_listing_tab_name' => 'addons',
        'addon_listing_tab_title' => 'addons',
        'addon_listing_tab_icon' => 'fa fa-plus',
        'file_headers' => array(),
        'show_category_count' => true,
    )

);
```
