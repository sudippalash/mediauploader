<?php

namespace Sudip\MediaUploader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class MediaUploader.
 *
 * @method private \Sudip\MediaUploader\MediaUploader makeDir(string $path)
 * @method private \Sudip\MediaUploader\MediaUploader makeFileName(string  $originalName, string  $ext, string|null  $name, string  $realPath)
 * @method public \Sudip\MediaUploader\MediaUploader thumb(string  $path, string  $file, boolean  $thumbPath, integer  $thumbWidth, integer  $thumbHeight)
 * @method public \Sudip\MediaUploader\MediaUploader imageUpload(\Illuminate\Http\UploadedFile  $file, string  $path, boolean  $thumb, string|null  $name, array  $imageResize, array  $thumbResize)
 * @method public \Sudip\MediaUploader\MediaUploader anyUpload(\Illuminate\Http\UploadedFile  $file, string  $path, string|null  $name)
 * @method public \Sudip\MediaUploader\MediaUploader base64ImageUpload(string  $requestFile, string  $path, boolean  $thumb, string|null  $name, array  $imageResize, array  $thumbResize)
 * @method public \Sudip\MediaUploader\MediaUploader imageUploadFromUrl(string  $requestFile, string  $path, boolean  $thumb, string|null  $name, array  $imageResize, array  $thumbResize)
 * @method public \Sudip\MediaUploader\MediaUploader contentUpload(string  $content, string  $path, string|null  $name, string  $extension)
 * @method public \Sudip\MediaUploader\MediaUploader webpUpload(string  $file, string  $path, boolean  $thumb, string|null  $name, array  $imageResize, array  $thumbResize)
 * @method public \Sudip\MediaUploader\MediaUploader delete(string  $path, string  $file, boolean  $thumb)
 * @method public \Sudip\MediaUploader\MediaUploader removeDirectory(string  $path)
 * @method public \Sudip\MediaUploader\MediaUploader fileExists(string  $path, string  $name)
 * @method public \Sudip\MediaUploader\MediaUploader showUrl(string  $path, string  $name)
 * @method public \Sudip\MediaUploader\MediaUploader showFile(string  $path, string  $name)
 * @method public \Sudip\MediaUploader\MediaUploader showImg(string  $path, string  $name, array|null  $array)
 *
 * @see \Sudip\MediaUploader\MediaUploader
 */
class MediaUploader extends Facade
{
    /**
     * Return facade accessor
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mediauploader';
    }
}
