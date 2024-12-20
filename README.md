## Media Uploader

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

`mediauploader` is a simple file-handling package for `Laravel` that provides various options for managing files, including image uploads, file uploads, WebP uploads, base64 image uploads, image uploads from URLs, and Google image content uploads. The package currently supports `public`, `local`, and `s3` storage (note: only publicly accessible `S3` buckets are supported).

It also includes file and image preview functionality.

## Install

Via Composer

```bash
composer require sudippalash/mediauploader
```

#### Publish config file

You should publish the config file with:

```
php artisan vendor:publish --provider="Sudip\MediaUploader\Providers\AppServiceProvider" --tag=config
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

    'base_dir' => null,

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
    | fake_image_url , if you specify a fake image path or url here. the entire package will use
    | this image when there is no image found. or you can specify the fake image in the
    | function parameter as well.
    | Example: 'fake_image_url' => 'images/fake.png' or 'fake_image_url' => 'https://example.com/images/fake.png,
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

## Configuration 
Before using this, you must complete the following steps (if you want to use the default filesystem disk, which is public):

1. Change your env filesystem drive to this if you want to use public storage directory.
```
FILESYSTEM_DISK=public
```
2. Please make sure to link your storage before using this package
```bash
php artisan storage:link
```

3. You can also specify the filesystem disk when using image or file functions (see the usage section for details).
```bash
MediaUploader::disk('local')->usageFunction();
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

4. Image Upload from URL

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

5. Base64 image Upload

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


6. Content Upload

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

7. Thumb (to create a thumb from an existing image, you can use this)

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

## Usage (FIle or Image Exists check)

1. FIle/Image Exists

```php
MediaUploader::fileExists(<path>, <file_name>)
```

Example:

```php
if(MediaUploader::fileExists('images', $file_name)) {
    // 
}
```

## Usage (FIle or Image URL & Preview)

1. FIle or Image Url

```php
MediaUploader::showUrl(<path>, <file_name>)
```

Example:

```php
{!! MediaUploader::showUrl('images', $file_name) !!}
```

2. File Preview

```php
MediaUploader::showFile(<path>, <file_name>, <file_not_found_text|optional>)
```

Example:

```php
{!! MediaUploader::showFile('images', $file_name, $empty_text) !!}
```


3. File Preview from url

```php
MediaUploader::showFileFromUrl(<url>, <file_name|optional>, <file_not_found_text|optional>)
```

Example:

```php
{!! MediaUploader::showFileFromUrl($file_url, $file_name, $empty_text) !!}
```

4. Image Preview

```php
MediaUploader::showImg(<path_or_url>, <file_name>, <array options>)
```

Example:

```php
{!! MediaUploader::showImg($pathOrUrl, $file_name, [
    'thumb' => true, // If thumb image store via upload function.
    'popup' => true, // Currently support only jQuery fancybox.
    'fakeImg' => 'images/avatar.png', // Pass fake image path without url or pass true (if pass true it will generate fake image from config file fake_image_url value).
    'id' => 'image',
    'class' => 'img-fluid',
    'style' => '',
    'alt' => 'Nice Image',
]) !!}
```

[ico-version]: https://img.shields.io/packagist/v/sudippalash/mediauploader?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sudippalash/mediauploader?style=flat-square
[link-packagist]: https://packagist.org/packages/sudippalash/mediauploader
[link-downloads]: https://packagist.org/packages/sudippalash/mediauploader
[link-author]: https://github.com/sudippalash