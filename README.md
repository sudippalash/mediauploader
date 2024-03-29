## mediauploader comes to Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]


`mediauploader` is a simple file handling package of `Laravel` that provides variety of options to deal with files. such as Image upload, file upload, webp upload, 
base64 image, upload from URL, and upload any type of contents

## Install

Via Composer

```bash
$ composer require sudippalash/mediauploader
```

#### For Laravel <= 5.4

After updating composer, add the ServiceProvider to the providers array in config/app.php

```php
Sudip\MediaUploader\MediaUploaderServiceProvider::class,
```

You can use the facade for shorter code. Add this to your aliases:

```php
'MediaUploader' => Sudip\MediaUploader\Facades\MediaUploader::class,
```

#### Publish config file

You will need to publish config file to add `mediauploader` global path.

```
php artisan vendor:publish --provider="Sudip\MediaUploader\MediaUploaderServiceProvider" --tag=config
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
        | thumb_dir creates another folder inside the directory as a "thumb" by default
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
        | specify the thumb image ratio of height and weight by default it takes 300px X 300px
        */

        'image_thumb_height' => 300,
        'image_thumb_width' => 300,

        /*
        |--------------------------------------------------------------------------
        | Fake Image Url
        |--------------------------------------------------------------------------
        |
        | fake_image_url , if you specify a fake image path here. the entire package will use
        | this image when there is no image found. or you can specify the fake image in the
        | function parameter as well.
        | Example: 'fake_image_url' => url('images/fake.png'),
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
```

## Configuartion Important
Before jumping to use this, you must do the following:

1. Please make sure to link your storage before using this package
```bash
php artisan storage:link
```
2. Change your env filesystem drive to this
```
FILESYSTEM_DISK=public
```

## Usage (File Upload)
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


7. Content Upload

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
        "name" => "example-image.jpg"
        "originalName" => "example-image.jpg"
        "size" => 148892
        "width" => 600
        "height" => 600
        "mime_type" => "image/jpeg"
        "ext" => "jpg"
        "url" => "http://localhost/storage/test/example-image.jpg"
    ]
```

## Usage (File Delete)

File Delete

```php
 MediaUploader::delete(<path>, <file_name>, $thumb = false)
```

Example:

```php
    $file = MediaUploader::delete('images', $file_name, true);

    if ($file) {
        // File is deleted successfully
    }
```

## Usage (Directory Delete)

Directory Delete

```php
 MediaUploader::removeDirectory(<path>)
```

Example:

```php
    $path = MediaUploader::removeDirectory('images');

    if ($path) {
        // Directory is deleted successfully
    }
```

## Usage (FIle/Image Exists, FIle/Image URL, File Preview & Image Preview)

1. FIle/Image Exists

```php
 MediaUploader::fileExists(<path>, <file_name>)
```

Example:

```php
    {!! MediaUploader::fileExists('images', $file_name) !!}
```

2. FIle/Image Url

```php
 MediaUploader::showUrl(<path>, <file_name>)
```

Example:

```php
    {!! MediaUploader::showUrl('images', $file_name) !!}
```

3. File Preview

```php
 MediaUploader::showFile(<path>, <file_name>)
```

Example:

```php
    {!! MediaUploader::showFile('images', $file_name) !!}
```

4. Image Preview

```php
 MediaUploader::showImg(<path>, <file_name>, <array options>)
```

Example:

```php
    {!! MediaUploader::showImg('images', $file_name, [
        'thumb' => true, // If thumb image store via upload function.
        'popup' => true, // Currently support only jQuery fancybox.
        'fakeImg' => 'images/avatar.png', // Pass fake image path without url or pass true (if pass true it will generate fake image from config file fake_image_url value).
        'id' => 'image',
        'class' => 'img-fluid',
        'style' => '',
        'alt' => 'Nice Image',
    ]) !!}
```