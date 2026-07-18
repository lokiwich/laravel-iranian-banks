<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table name
    |--------------------------------------------------------------------------
    |
    | The database table used to store Iranian bank records.
    |
    */

    'table' => 'iranian_banks',

    /*
    |--------------------------------------------------------------------------
    | Auto seed
    |--------------------------------------------------------------------------
    |
    | When true, running `php artisan iranian-banks:install` will seed the
    | default Shetab bank dataset after migrating.
    |
    */

    'auto_seed' => true,

    /*
    |--------------------------------------------------------------------------
    | SVG path
    |--------------------------------------------------------------------------
    |
    | Absolute path to the directory containing bank SVG icons.
    | Defaults to the package resources/svg directory.
    |
    */

    'svg_path' => null,

];
