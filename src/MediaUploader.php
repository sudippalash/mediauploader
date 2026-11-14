<?php

namespace Sudip\MediaUploader;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\FileExtension;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use InvalidArgumentException;

class MediaUploader
{
    protected string $diskName;

    protected string $basePath;

    protected string $thumbDir;

    public function __construct()
    {
        $this->diskName = config('mediauploader.disk') ?: config('filesystems.default');
        $this->basePath = trim((string) config('mediauploader.base_dir'), '/');
        $this->thumbDir = trim((string) config('mediauploader.thumb_dir', 'thumb'), '/');
    }

    /**
     * Use another disk. Returns a copy so the facade keeps its default disk.
     */
    public function disk(string $diskName): static
    {
        $uploader = clone $this;
        $uploader->diskName = $diskName;

        return $uploader;
    }

    public function makeDir(string $path): string
    {
        $realPath = $this->path($path);

        if (! $this->exists($realPath)) {
            $this->storage()->makeDirectory($realPath);
        }

        return $realPath;
    }

    /**
     * Upload any file type.
     */
    public function anyUpload(UploadedFile $file, string $path, ?string $name = null): array|false
    {
        $realPath = $this->path($path);
        $ext = strtolower($file->getClientOriginalExtension()) ?: (string) $file->guessExtension();
        $fileName = $this->makeFileName($file->getClientOriginalName(), $ext, $name, $realPath);

        if (! $this->storage()->putFileAs($realPath, $file, $fileName)) {
            return false;
        }

        return [
            'name' => $fileName,
            'originalName' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'width' => null,
            'height' => null,
            'mime_type' => $file->getMimeType(),
            'ext' => $ext,
            'url' => $this->getUrl($realPath.'/'.$fileName),
        ];
    }

    /**
     * Upload an image, optionally resized and with a thumb.
     */
    public function imageUpload(UploadedFile $file, string $path, bool $thumb = false, ?string $name = null, array $imageResize = [], array $thumbResize = [0, 0]): array|false
    {
        $ext = $this->imageExtension($file->getClientOriginalExtension(), $file->getMimeType());
        $image = $this->images()->read($file->getRealPath());

        return $this->saveImage($image, $path, $ext, $file->getClientOriginalName(), $name, $thumb, $imageResize, $thumbResize);
    }

    /**
     * Upload an image converted to webp.
     */
    public function webpUpload(UploadedFile $file, string $path, bool $thumb = false, ?string $name = null, array $imageResize = [], array $thumbResize = [0, 0]): array|false
    {
        $image = $this->images()->read($file->getRealPath());

        return $this->saveImage($image, $path, 'webp', $file->getClientOriginalName(), $name, $thumb, $imageResize, $thumbResize);
    }

    /**
     * Upload a base64 image. Accepts a data uri or a plain base64 string.
     */
    public function base64ImageUpload(string $requestFile, string $path, bool $thumb = false, ?string $name = null, array $imageResize = [], array $thumbResize = [0, 0]): array|false
    {
        $base64 = preg_replace('#^data:[^;]+;base64,#i', '', trim($requestFile));
        $content = base64_decode($base64, true);

        if (! $content) {
            throw new InvalidArgumentException('Invalid base64 image.');
        }

        $ext = $this->imageExtension(null, $this->mimeType($content));
        $image = $this->images()->read($content);

        return $this->saveImage($image, $path, $ext, null, $name, $thumb, $imageResize, $thumbResize);
    }

    /**
     * Download an image from a http(s) url and upload it.
     */
    public function imageUploadFromUrl(string $requestFile, string $path, bool $thumb = false, ?string $name = null, array $imageResize = [], array $thumbResize = [0, 0]): array|false
    {
        $content = $this->download($requestFile);

        if ($content === null) {
            return false;
        }

        $urlExt = pathinfo((string) parse_url($requestFile, PHP_URL_PATH), PATHINFO_EXTENSION);
        $ext = $this->imageExtension($urlExt, $this->mimeType($content));
        $image = $this->images()->read($content);

        return $this->saveImage($image, $path, $ext, null, $name, $thumb, $imageResize, $thumbResize);
    }

    /**
     * Save raw content to a file.
     */
    public function contentUpload(string $content, string $path, ?string $name = null, string $extension = 'jpg'): array|false
    {
        $realPath = $this->path($path);
        $ext = strtolower(ltrim($extension, '.'));
        $fileName = $this->makeFileName(null, $ext, $name, $realPath);

        if (! $this->storage()->put($realPath.'/'.$fileName, $content)) {
            return false;
        }

        return [
            'name' => $fileName,
            'originalName' => null,
            'size' => strlen($content),
            'width' => null,
            'height' => null,
            'mime_type' => $this->mimeType($content),
            'ext' => $ext,
            'url' => $this->getUrl($realPath.'/'.$fileName),
        ];
    }

    /**
     * Create a thumb for an existing image.
     *
     * @param  string|bool|null  $thumbPath  custom thumb folder name, false uses thumb_dir
     */
    public function thumb(string $path, string $file, string|bool|null $thumbPath = false, int $thumbWidth = 0, int $thumbHeight = 0): bool
    {
        $source = $this->path($path, $file);

        if (! $this->exists($source)) {
            return false;
        }

        $width = $thumbWidth ?: (int) config('mediauploader.image_thumb_width');
        $height = $thumbHeight ?: (int) config('mediauploader.image_thumb_height');
        $folder = is_string($thumbPath) && $thumbPath !== '' ? $thumbPath : $this->thumbDir;
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        $image = $this->images()->read($this->storage()->get($source));

        if ($width && $height) {
            // Fit inside the box without upscaling and fill the rest with transparency.
            $image->pad($width, $height, 'transparent');
        } else {
            $image->scaleDown($width ?: null, $height ?: null);
        }

        $encoded = $image->encodeByExtension($ext, quality: $this->quality($ext));

        return $this->storage()->put($this->path($path, $folder, $file), (string) $encoded);
    }

    /**
     * Delete a file and optionally its thumb.
     */
    public function delete(string $path, string $file, bool $thumb = false): bool
    {
        if ($file === '') {
            return false;
        }

        if ($thumb) {
            $this->storage()->delete($this->path($path, $this->thumbDir, $file));
        }

        $realPath = $this->path($path, $file);

        if (! $this->exists($realPath)) {
            return false;
        }

        return $this->storage()->delete($realPath);
    }

    /**
     * Delete a directory. An empty path is refused so the base directory can't be wiped.
     */
    public function removeDirectory(string $path): bool
    {
        if (trim($path, '/ ') === '') {
            return false;
        }

        $realPath = $this->path($path);

        if (! $this->exists($realPath)) {
            return false;
        }

        return $this->storage()->deleteDirectory($realPath);
    }

    public function fileExists(string $path, ?string $name): bool
    {
        return $name && $this->exists($this->path($path, $name));
    }

    public function showUrl(string $path, ?string $name): ?string
    {
        if (! $this->fileExists($path, $name)) {
            return null;
        }

        return $this->getUrl($this->path($path, $name));
    }

    /**
     * Link to a file.
     */
    public function showFile(string $path, ?string $name, ?string $emptyText = null): ?string
    {
        if (! $this->fileExists($path, $name)) {
            return $emptyText;
        }

        $url = $this->getUrl($this->path($path, $name));

        return '<a href="'.e($url).'" target="_blank">'.e($name).'</a>';
    }

    /**
     * Link to a url.
     */
    public function showFileFromUrl(?string $url, ?string $name = null, ?string $emptyText = null): ?string
    {
        if (! $url || ! $this->isSafeUrl($url)) {
            return $emptyText;
        }

        return '<a href="'.e($url).'" target="_blank">'.e($name ?? $url).'</a>';
    }

    /**
     * Image tag for a stored image or a url.
     *
     * Options: thumb, popup, fakeImg, id, class, style, alt
     */
    public function showImg(?string $pathOrUrl, ?string $name, ?array $array = null): ?string
    {
        $options = $array ?? [];
        $imgUrl = null;
        $imgSrc = null;

        if ($pathOrUrl && filter_var($pathOrUrl, FILTER_VALIDATE_URL) && $this->isSafeUrl($pathOrUrl)) {
            $imgUrl = $imgSrc = $pathOrUrl;
        } elseif ($pathOrUrl !== null && $this->fileExists($pathOrUrl, $name)) {
            $imgUrl = $imgSrc = $this->getUrl($this->path($pathOrUrl, $name));

            $thumbPath = $this->path($pathOrUrl, $this->thumbDir, $name);
            if (! empty($options['thumb']) && $this->exists($thumbPath)) {
                $imgSrc = $this->getUrl($thumbPath);
            }
        } else {
            $imgSrc = $this->fakeImage($options['fakeImg'] ?? null);
        }

        if (! $imgSrc) {
            return null;
        }

        $img = '<img src="'.e($imgSrc).'"';
        foreach (['alt', 'class', 'id', 'style'] as $attribute) {
            if (isset($options[$attribute])) {
                $img .= ' '.$attribute.'="'.e($options[$attribute]).'"';
            }
        }
        $img .= '>';

        // Popup only for real images, not for the fake one.
        if (($options['popup'] ?? false) === true && $imgUrl) {
            $caption = e($options['alt'] ?? '');

            return '<a href="'.e($imgUrl).'" data-caption="'.$caption.'" data-fancybox="group">'.$img.'</a>';
        }

        return $img;
    }

    /**
     * Resize, encode and store an image, then create the thumb.
     */
    protected function saveImage(ImageInterface $image, string $path, string $ext, ?string $originalName, ?string $name, bool $thumb, array $imageResize, array $thumbResize): array|false
    {
        $realPath = $this->path($path);
        $fileName = $this->makeFileName($originalName, $ext, $name, $realPath);

        // scaleDown keeps the proportions and never enlarges; resize() would stretch the image.
        if ($imageResize) {
            $width = $imageResize[0] ?? null;
            $height = $imageResize[1] ?? null;

            $image->scaleDown($width ?: null, $height ?: null);
        }

        $encoded = $image->encodeByExtension($ext, quality: $this->quality($ext));

        if (! $this->storage()->put($realPath.'/'.$fileName, (string) $encoded)) {
            return false;
        }

        if ($thumb) {
            $this->thumb($path, $fileName, false, (int) ($thumbResize[0] ?? 0), (int) ($thumbResize[1] ?? 0));
        }

        return [
            'name' => $fileName,
            'originalName' => $originalName,
            'size' => $encoded->size(),
            'width' => $image->width(),
            'height' => $image->height(),
            'mime_type' => $encoded->mimetype(),
            'ext' => $ext,
            'url' => $this->getUrl($realPath.'/'.$fileName),
        ];
    }

    /**
     * Make a unique, slugged file name. Adds -1, -2, ... when the name is taken.
     */
    protected function makeFileName(?string $originalName, string $ext, ?string $name, string $realPath): string
    {
        if (config('mediauploader.random_name')) {
            $baseName = Str::lower(Str::random(32));
        } else {
            $baseName = Str::slug($name ?: pathinfo((string) $originalName, PATHINFO_FILENAME)) ?: uniqid();

            if (config('mediauploader.timestamp_prefix')) {
                $baseName .= '-'.time();
            }
        }

        $ext = $ext ? '.'.$ext : '';
        $fileName = $baseName.$ext;
        $counter = 1;

        while ($this->exists($realPath.'/'.$fileName)) {
            $fileName = $baseName.'-'.$counter++.$ext;
        }

        return $fileName;
    }

    protected function getUrl(string $path): string
    {
        $path = ltrim($path, '/');

        // Private local disk has no public url, serve through the package route.
        if ($this->usesPreviewRoute()) {
            return route('mediauploader.preview', ['path' => $path]);
        }

        $minutes = (int) config('mediauploader.temporary_url_minutes');
        if ($minutes && $this->storage()->providesTemporaryUrls()) {
            return $this->storage()->temporaryUrl($path, now()->addMinutes($minutes));
        }

        return $this->storage()->url($path);
    }

    protected function usesPreviewRoute(): bool
    {
        $disk = config('filesystems.disks.'.$this->diskName, []);

        return config('mediauploader.route.enabled', false)
            && $this->diskName === config('mediauploader.route.disk', 'local')
            && ($disk['driver'] ?? null) === 'local'
            && empty($disk['url']);
    }

    /**
     * Build a path inside base_dir. Throws on "." or ".." segments.
     */
    protected function path(?string ...$segments): string
    {
        $parts = [];

        foreach ([$this->basePath, ...$segments] as $segment) {
            foreach (explode('/', str_replace('\\', '/', (string) $segment)) as $part) {
                if ($part === '.' || $part === '..') {
                    throw new InvalidArgumentException('Invalid path: '.implode('/', $segments));
                }

                if ($part !== '') {
                    $parts[] = $part;
                }
            }
        }

        return implode('/', $parts);
    }

    /**
     * Get an image extension Intervention can encode, from the given extension or mime type.
     */
    protected function imageExtension(?string $ext, ?string $mimeType): string
    {
        $extension = FileExtension::tryCreate(strtolower((string) $ext))
            ?? FileExtension::tryCreate(strtolower((string) $mimeType));

        if (! $extension) {
            throw new InvalidArgumentException('Unsupported image type: '.($mimeType ?: $ext));
        }

        return $extension->value;
    }

    protected function quality(string $ext): int
    {
        if (strtolower($ext) === 'webp') {
            return (int) config('mediauploader.webp_quality', 70);
        }

        return (int) config('mediauploader.image_quality', 90);
    }

    protected function mimeType(string $content): ?string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->buffer($content) ?: null;
    }

    /**
     * Download a remote file. Every url (including redirects) is checked with checkRemoteUrl().
     */
    protected function download(string $url): ?string
    {
        $this->checkRemoteUrl($url);

        $maxSize = (int) config('mediauploader.remote.max_size', 10 * 1024 * 1024);

        $response = Http::timeout((int) config('mediauploader.remote.timeout', 15))
            ->withOptions([
                'allow_redirects' => [
                    'max' => 5,
                    'on_redirect' => fn ($request, $response, $uri) => $this->checkRemoteUrl((string) $uri),
                ],
            ])
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        if (strlen($response->body()) > $maxSize) {
            throw new InvalidArgumentException('Remote file is larger than '.$maxSize.' bytes.');
        }

        return $response->body();
    }

    /**
     * Only allow http(s) urls that don't point to a private or reserved ip.
     */
    protected function checkRemoteUrl(string $url): void
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = trim((string) parse_url($url, PHP_URL_HOST), '[]');

        if (! in_array($scheme, ['http', 'https']) || $host === '') {
            throw new InvalidArgumentException('Only http and https urls are allowed.');
        }

        if (config('mediauploader.remote.allow_private_hosts')) {
            return;
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : gethostbynamel($host);

        if (! $ips) {
            throw new InvalidArgumentException('Unable to resolve host: '.$host);
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new InvalidArgumentException('Private or reserved address is not allowed: '.$host);
            }
        }
    }

    /**
     * Allow http, https and relative urls in links (blocks javascript: etc).
     */
    protected function isSafeUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        return $scheme === null || in_array(strtolower($scheme), ['http', 'https']);
    }

    protected function fakeImage(mixed $fakeImg): ?string
    {
        if (is_string($fakeImg) && $fakeImg !== '') {
            return $this->isSafeUrl($fakeImg) ? $fakeImg : null;
        }

        $configUrl = config('mediauploader.fake_image_url');

        if (! $fakeImg || ! $configUrl) {
            return null;
        }

        return filter_var($configUrl, FILTER_VALIDATE_URL) ? $configUrl : url($configUrl);
    }

    protected function images(): ImageManager
    {
        $driver = config('mediauploader.image_driver') === 'imagick' ? new ImagickDriver : new GdDriver;

        return new ImageManager($driver);
    }

    protected function storage(): FilesystemAdapter
    {
        return Storage::disk($this->diskName);
    }

    protected function exists(string $path): bool
    {
        return $this->storage()->exists($path);
    }
}
