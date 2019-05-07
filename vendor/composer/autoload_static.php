<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit750139ae0716699ff0ff76042ed72f27
{
    public static $files = array (
        'cf65e956ef36045d86a2d654adf24874' => __DIR__ . '/..' . '/varunsridharan/sweetalert2-php/sweetalert2.php',
        '011316cc7fd7a11c4bebeb6bcdea5621' => __DIR__ . '/..' . '/varunsridharan/wp-dependencies/src/dependencies.php',
        '3137c1182dbb8ff6553f65428ecc96ee' => __DIR__ . '/../..' . '/vsp-init.php',
    );

    public static $prefixLengthsPsr4 = array (
        '\\' => 
        array (
            '\\' => 1,
        ),
        'V' => 
        array (
            'Varunsridharan\\WordPress\\' => 25,
            'Varunsridharan\\PHP\\' => 19,
        ),
        'T' => 
        array (
            'TheLeague\\Database\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        '\\' => 
        array (
            0 => __DIR__ . '/..' . '/varunsridharan/wpallimport_rapidaddon/src',
        ),
        'Varunsridharan\\WordPress\\' => 
        array (
            0 => __DIR__ . '/..' . '/varunsridharan/wp-ajaxer/src',
            1 => __DIR__ . '/..' . '/varunsridharan/wp-db-table/src',
            2 => __DIR__ . '/..' . '/varunsridharan/wp-localizer/src',
            3 => __DIR__ . '/..' . '/varunsridharan/wp-post/src',
            4 => __DIR__ . '/..' . '/varunsridharan/wp-review-me/src',
            5 => __DIR__ . '/..' . '/varunsridharan/wp-transient-api/src',
        ),
        'Varunsridharan\\PHP\\' => 
        array (
            0 => __DIR__ . '/..' . '/varunsridharan/php-autoloader/src',
        ),
        'TheLeague\\Database\\' => 
        array (
            0 => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src',
        ),
    );

    public static $classMap = array (
        'TheLeague\\Database\\Database' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/class-database.php',
        'TheLeague\\Database\\Query_Builder' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/class-query-builder.php',
        'TheLeague\\Database\\Traits\\Escape' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/traits/class-escape.php',
        'TheLeague\\Database\\Traits\\GroupBy' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/traits/class-groupby.php',
        'TheLeague\\Database\\Traits\\OrderBy' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/traits/class-orderby.php',
        'TheLeague\\Database\\Traits\\Select' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/traits/class-select.php',
        'TheLeague\\Database\\Traits\\Translate' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/traits/class-translate.php',
        'TheLeague\\Database\\Traits\\Where' => __DIR__ . '/..' . '/thewpleague/wp-query-builder/src/traits/class-where.php',
        'WP_Async_Request' => __DIR__ . '/..' . '/a5hleyrich/wp-background-processing/classes/wp-async-request.php',
        'WP_Background_Process' => __DIR__ . '/..' . '/a5hleyrich/wp-background-processing/classes/wp-background-process.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit750139ae0716699ff0ff76042ed72f27::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit750139ae0716699ff0ff76042ed72f27::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit750139ae0716699ff0ff76042ed72f27::$classMap;

        }, null, ClassLoader::class);
    }
}
