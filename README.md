## mediauploader comes to Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]


`mediauploader` is a simple file handling package of `Laravel` that provides variety of options to deal with files. such as Image upload, file upload, webp upload, 
base64 image, upload from URL, and upload any type of contents

## Install

Via Composer

```bash
$ composer require sudip-palash/mediauploader
```

#### For Laravel <= 5.4

After updating composer, add the ServiceProvider to the providers array in config/app.php

```php
Sudip\MediaUploder\MediaUploderServiceProvider::class,
```

You can use the facade for shorter code. Add this to your aliases:

```php
'MediaUploader' => Sudip\MediaUploder\Facades\MediaUploader::class,
```

#### Publish config file

You will need to publish config file to add `mediauploader` global path.

```
php artisan vendor:publish --provider="Sudip\MediaUploder\MediaUploderServiceProvider" --tag=mediauploader
```

In `config/mediauploader.php` config file you should set `mediauploader` global path.

```php
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
```

## Configuartion Important
Before jumping to use this, you must do the following:

1. Please make sure to link your storage before using this package
```bash
php artisan storage:link
```
2. Change your env filesystem drive to this
```
FILESYSTEM_DRIVER=public
```

## Usage
1. Image Upload

```php
 MediaUploader::imageUpload(<UploadedFile image>, <output path>, thumb=false, name=null, $imageResize = [], $thumbResize = [0, 0]);
```

Example:

```php
    $file = MediaUploader::imageUpload($request->file, 'images', 1, null, [600, 600]);

    if ($file) {
        // File is saved successfully
    }
```
2. Webp Image Upload

```php
 MediaUploader::webpUpload(<UploadedFile image>, <output path>, thumb=false, name=null, $imageResize = [], $thumbResize = [0, 0]);
```

Example:

```php
    $file = MediaUploader::webpUpload($request->file, 'webps', 1, null, [600, 600]);

    if ($file) {
        // File is saved successfully
    }
```

3. Any Upload (if you are not sure what type of file is, you can use this)

```php
 MediaUploader::anyUpload(<UploadedFile file>, <output path>, name=null);
```

Example:

```php
    $file = MediaUploader::anyUpload($request->file, 'files', 'filename');

    if ($file) {
        // File is saved successfully
    }
```

4. Thumb (to create a thumb from an existing image, you can use this)

```php
 MediaUploader::thumb(<output path>, <file>, $thumbPath = false, $thumbWidth = 0, $thumbHeight = 0);
```

Example:

```php
    $file = MediaUploader::thumb('thumbs', $file, 200, 200);

    if ($file) {
        // File is saved successfully
    }
```

5. Image Upload from URL

```php
 MediaUploader::imageUploadFromUrl(<Valid Image URL>, <output path>, thumb=false, name=null, $imageResize = [], $thumbResize = [0, 0]);
```

Example:

```php
    $file = MediaUploader::imageUploadFromUrl($url, 'images', 1, null, [600, 600]);

    if ($file) {
        // File is saved successfully
    }
```

6. Base64 image Upload

```php
 MediaUploader::base64ImageUpload(<base64 Content>, <output path>, thumb=false, name=null, $imageResize = [], $thumbResize = [0, 0]);
```

Example:

```php
    $file = MediaUploader::base64ImageUpload($content, 'images', 1, null, [600, 600]);

    if ($file) {
        // File is saved successfully
    }
```


7. Base64 image Upload

```php
 MediaUploader::contentUpload(<Content>, <output path>, name=null);
```

Example:

```php
    $file = MediaUploader::contentUpload($content, 'contents', 'name');

    if ($file) {
        // File is saved successfully
    }
```


Returns:

Response from every function looks like this
```php
    [
        "name" => "example-image.JPG"
        "originalName" => "example-image.JPG"
        "size" => 148892
        "width" => 600
        "height" => 600
        "mime_type" => "image/jpeg"
        "ext" => "JPG"
        "url" => "http://localhost/1-test/example-pack/public/storage/test/example-image.JPG"
    ]
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/sudippalash/mediauploader?style=flat-square
[ico-license]: https://img.shields.io/github/license/sudippalash/mediauploader?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sudippalash/mediauploader?style=flat-square
[link-packagist]: https://packagist.org/packages/sudip-palash/mediauploader
[link-downloads]: https://packagist.org/packages/sudip-palash/mediauploader
[link-author]: https://github.com/sudippalash
