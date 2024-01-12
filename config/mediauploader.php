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
    | Timestamp Prefix
    |--------------------------------------------------------------------------
    |
    | If timestamp_prefix is true then create a file with a timestamp to ignore the same name image replacement. Example: image-1658562981.png.
    | If timestamp_prefix is false then the script checks file exists or not if the file exists then add the time() prefix for the new file otherwise leave it as the file
    | name. 
    */

    'timestamp_prefix' => false,

    /*
    |--------------------------------------------------------------------------
    | Thumb Image Height Width
    |--------------------------------------------------------------------------
    |
    | specify the thumb image ratio of height and weight by defualt it takes 300px X 300px
    */

    'image_thumb_height' => 300,
    'image_thumb_width' => 300,

    /*
    |--------------------------------------------------------------------------
    | Fake Image Url
    |--------------------------------------------------------------------------
    |
    | fake_image_url , if you specify a fake image path here. the enitre package will use 
    | this image when there is not image found. or you can speicify the fake image in the 
    | function parameter as well
    */

    'fake_image_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Folder permission
    |--------------------------------------------------------------------------
    |
    | path_permission , if you create a folder in your project then you can define your folder permission.
    | Example: null, 0755, 0777
    */

    'path_permission' => 0777,
];
