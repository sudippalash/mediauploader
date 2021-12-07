<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Directory
    |--------------------------------------------------------------------------
    |
    | base_dir stores all other directory inside storage folder of your laravel application by default
    | if you specify any name. all storage will be done inside that directory or name that you specified
    | 
    */

    'base_dir' => '',

    /*
    |--------------------------------------------------------------------------
    | Thumb Directory
    |--------------------------------------------------------------------------
    |
    | thumd_dir creates another folder inside the directory as a "thumb" by default 
    | you can change the name thumb to any other name you like. 
    */

    'thumb_dir' => 'thumb',

    /*
    |--------------------------------------------------------------------------
    | image_thumb_height thum
    |--------------------------------------------------------------------------
    |
    | specify the thum image ratio of height and weight
    |  for example . by defualt it takes 300X300
    */

    'image_thumb_height' => 300,
    'image_thumb_width' => 300,
];
