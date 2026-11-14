## Media Uploader

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

`mediauploader` is a simple file-handling package for `Laravel` that provides various options for managing files, including image uploads, file uploads, WebP uploads, base64 image uploads, image uploads from URLs, and Google image content uploads. The package currently supports `public`, `local`, and `s3` storage (note: only publicly accessible `S3` buckets are supported).

It also includes file and image preview functionality.

## Requirements

| mediauploader | PHP | Laravel | Intervention Image |
|---|---|---|---|
| 3.x | 8.2+ | 10, 11, 12, 13 | 3.x |
| 1.x, 2.x | 7.3+ | 7 – 13 | 2.x |

Upgrading from 2.x? See [CHANGELOG.md](CHANGELOG.md#upgrading-from-2x).

## Install

Via Composer

```bash
composer require sudippalash/mediauploader
```

#### Publish config file

You should publish the config file with:

```
php artisan vendor:publish --tag=mediauploader-config
```

In `config/mediauploader.php` you can set:

| Key | Default | Description |
|---|---|---|
| `disk` | `null` | Disk to store files on. `null` uses `filesystems.default`. Env: `MEDIAUPLOADER_DISK`. |
| `base_dir` | `null` | Every path is stored inside this folder, e.g. `uploads` → `uploads/images/photo.jpg`. |
| `thumb_dir` | `thumb` | Sub folder name for thumbs. |
| `timestamp_prefix` | `false` | Append `-<timestamp>` to generated names. |
| `random_name` | `false` | Use a random 32 character name instead of the original name. |
| `image_driver` | `gd` | `gd` or `imagick`. |
| `image_quality` / `webp_quality` | `90` / `70` | Encoding quality for lossy formats. |
| `image_thumb_width` / `image_thumb_height` | `300` / `300` | Default thumb size. Set one to `null` to scale by the other only. |
| `fake_image_url` | `null` | Placeholder used by `showImg()` with `'fakeImg' => true`. |
| `temporary_url_minutes` | `null` | Return signed temporary urls on disks that support them (e.g. private S3). |
| `remote.timeout` / `remote.max_size` | `15` / `10 MB` | Limits for `imageUploadFromUrl()`. |
| `remote.allow_private_hosts` | `false` | Allow `imageUploadFromUrl()` to fetch from private / internal IPs. |
| `route.enabled` / `route.prefix` / `route.disk` / `route.middleware` | `false` / `mediauploader` / `local` / `[]` | Preview route for files on a private local disk. Off by default. |

When a file with the same name already exists, `-1`, `-2`, ... is appended, so existing files are never overwritten.

## Configuration 
Before using this, you must complete the following steps (if you want to use the default filesystem disk, which is public):

1. Change your env filesystem drive to this if you want to use public storage directory (or set `MEDIAUPLOADER_DISK=public` to change it only for this package).
```
FILESYSTEM_DISK=public
```
2. Please make sure to link your storage before using this package
```bash
php artisan storage:link
```

3. You can also specify the filesystem disk for a single call. It does not change the disk used by later calls.
```php
MediaUploader::disk('s3')->imageUpload($request->file('image'), 'images');
```

4. Files on the private `local` disk have no public url. To serve them, turn on the preview route (`'route' => ['enabled' => true]`), which adds `GET /mediauploader/{path}`. It only serves files inside `base_dir`. **Set a `base_dir` and add middleware** (for example `['web', 'auth']`) in `route.middleware`: without them, anyone could read that disk.

## Usage (File Upload)

Invalid input (unsupported image type, invalid base64, `..` in a path, a disallowed url) throws an `InvalidArgumentException`.

`$imageResize` is `[maxWidth, maxHeight]`: the image is scaled down to fit inside that box, keeping its proportions, and is never enlarged. Pass one value (`[600]`) to limit only the width.

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

4. Image Upload from URL (only `http`/`https`; private and internal addresses are refused by default)

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

5. Base64 image Upload (a data URI like `data:image/png;base64,...` or raw base64)

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
MediaUploader::contentUpload(<Content>, <output path>, name=null, extension='jpg');
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
MediaUploader::thumb(<path>, <file>, $thumbFolder = false, $thumbWidth = 0, $thumbHeight = 0);
```

`$thumbFolder` is a custom folder name; `false` uses `thumb_dir`. Returns `true` or `false`.

Example:

```php
$file = MediaUploader::thumb('images', 'photo.jpg', false, 200, 200);

if ($file) {
    // File is saved successfully
}
```


Returns:

Every upload function returns this array (or `false` when the disk could not write the file)
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

4. Image Preview (all attributes are HTML-escaped)

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

## Testing

```bash
composer test
composer analyse
composer lint
```

[ico-version]: https://img.shields.io/packagist/v/sudippalash/mediauploader?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sudippalash/mediauploader?style=flat-square
[link-packagist]: https://packagist.org/packages/sudippalash/mediauploader
[link-downloads]: https://packagist.org/packages/sudippalash/mediauploader
[link-author]: https://github.com/sudippalash