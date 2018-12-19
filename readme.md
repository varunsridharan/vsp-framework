# VSP Framework
Simple & Lightweight WP Plugin Framework

## Installation
The preferred way to install this extension is through [Composer](http://getcomposer.org/download/).

To install **VSP_Framework library**, simply:

    $ composer require varunsridharan/vsp-framework

The previous command will only install the necessary files, if you prefer to **download the entire source code** you can use:

    $ composer require varunsridharan/vsp-framework --prefer-source

You can also **clone the complete repository** with Git:

    $ git clone https://github.com/varunsridharan/vsp-framework.git

Or **install it manually**:

[Download VSP_Framework.zip](https://github.com/varunsridharan/vsp-framework/archive/master.zip):

    $ wget https://github.com/varunsridharan/vsp-framework/archive/master.zip

## Usage
```php
require __DIR__ . '/vsp-framework/vsp-init.php

```


## Included Libs / Frameworks
```json
{
    "require" : {
        "wpbp/pointerplus"                      : "dev-master",
        "varunsridharan/wpallimport_rapidaddon" : "dev-master",
        "varunsridharan/wp-ajaxer"              : "^1.0",
        "varunsridharan/wp-endpoint"            : "^1.0",
        "varunsridharan/wp-post"                : "^1.0",
        "varunsridharan/wp-review-me"           : "^1.0",
        "varunsridharan/wp-transient-api"       : "^1.0",
        "varunsridharan/wp-db-table"            : "dev-master",
        "a5hleyrich/wp-background-processing"   : "^1.0.1"
    }
}
```

## Configs
```php
$config = array();
```

### Addons Module.
```php
/**
 * Plugin's Addon Module Configuration.
 * Config Options
 * array(
 *    'base_path'               => '',
 *    'base_url'                => '',
 *    'addon_listing_tab_name'  => 'addons',
 *    'addon_listing_tab_title' => 'Addons',
 *    'addon_listing_tab_icon'  => 'fa fa-plus',
 *    'file_headers'            => array(),
 *    'show_category_count'     => true,
 * )
 */
$config['addons'] = true;
```
### Settings / WPOnion Module
```php
/**
 * Settings Page Configuration.
 * Below arguments are related to WPOnion.
 * please refer https://github.com/wponion/wponion | https://docs.wponion.com for options informations.
 * basic required ars
 * array(
 *    'option_name' => '',
 *    'theme' => 'modern', #modern|fresh|your-theme
 * )
 *
 */
$config['settings_page'] = array(
    'option_name'     => 'vsp_sample_settings',
    'theme'           => 'modern',
    'menu'            => array(
        'menu_title' => __( 'VSP Sample' ),
        'page_title' => __( 'VSP Sample Plugin' ),
        'submenu'    => true,
    ),
    'framework_title' => __( 'Settings Page' ),
);
```

### System Tool Module
```php
/**
 * Config for system tools.
 * Possible Values : true / false / array()
 * array(
 *    'system_tools_menu' => true, # true/false/array of values
 *    'menu'              => true, # true/false
 *    'system_status'     => true, #true/false/array of values
 *    'logging'           => true, #true/false/array of values
 * )
 *
 * system_status /logging / system_tool_menu array data can be like below
 * array(
 *    'name' => '',
 *    'title' => '',
 *    'icon'=>''
 * )
 * The above array is related to WPOnion Page Argument.
 *
 * $config['system_tools'] = true;
 * $config['system_tools'] = false;
 * $config['system_tools'] = array(
 *    'menu' => array(
 *        'title' => __( 'Sys Tools' ),
 *    ),
 * );
 *
 */
$config['system_tools'] = true;
```

### WP Review Me Module
```php
/**
 * Custom Lib To popup a alert after x number of days to ask for plugin review.
 * please refer https://github.com/varunsridharan/wp-review-me for options informations.
 */
$config['VS_WP_Review_Me'] = true;
```

### Logging Module
```php
/**
 * Config to enable logging option.
 * if set to true. then it create a custom logger instance and saves it.
 */
$config['logging'] = true;
```