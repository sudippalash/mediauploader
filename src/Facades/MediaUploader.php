<?php

namespace Sudip\MediaUploader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Sudip\MediaUploader\MediaUploader disk(string $diskName)
 * @method static string makeDir(string $path)
 * @method static array<string, mixed>|false anyUpload(\Illuminate\Http\UploadedFile $file, string $path, string|null $name = null)
 * @method static array<string, mixed>|false imageUpload(\Illuminate\Http\UploadedFile $file, string $path, bool $thumb = false, string|null $name = null, array<int, int|null> $imageResize = [], array<int, int|null> $thumbResize = [0, 0])
 * @method static array<string, mixed>|false webpUpload(\Illuminate\Http\UploadedFile $file, string $path, bool $thumb = false, string|null $name = null, array<int, int|null> $imageResize = [], array<int, int|null> $thumbResize = [0, 0])
 * @method static array<string, mixed>|false base64ImageUpload(string $requestFile, string $path, bool $thumb = false, string|null $name = null, array<int, int|null> $imageResize = [], array<int, int|null> $thumbResize = [0, 0])
 * @method static array<string, mixed>|false imageUploadFromUrl(string $requestFile, string $path, bool $thumb = false, string|null $name = null, array<int, int|null> $imageResize = [], array<int, int|null> $thumbResize = [0, 0])
 * @method static array<string, mixed>|false contentUpload(string $content, string $path, string|null $name = null, string $extension = 'jpg')
 * @method static bool thumb(string $path, string $file, string|bool|null $thumbPath = false, int $thumbWidth = 0, int $thumbHeight = 0)
 * @method static bool delete(string $path, string $file, bool $thumb = false)
 * @method static bool removeDirectory(string $path)
 * @method static bool fileExists(string $path, string|null $name)
 * @method static string|null showUrl(string $path, string|null $name)
 * @method static string|null showFile(string $path, string|null $name, string|null $emptyText = null)
 * @method static string|null showFileFromUrl(string|null $url, string|null $name = null, string|null $emptyText = null)
 * @method static string|null showImg(string|null $pathOrUrl, string|null $name, array<string, mixed>|null $array = null)
 *
 * @see \Sudip\MediaUploader\MediaUploader
 */
class MediaUploader extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mediauploader';
    }
}
