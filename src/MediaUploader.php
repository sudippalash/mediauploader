<?php

namespace Sudip\MediaUploader;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaUploader
{
    /**
     * The storage folder name.
     *
     * @var string
     */
    public $diskName;

    /**
     * The base folder name.
     *
     * @var string
     */
    public $basePath;

    /**
     * The thumb folder name.
     *
     * @var string
     */
    public $thumbDir;

    /**
     * The folder permission.
     *
     * @var string
     */
    public $pathPermission;

    /**
     * Create a new media uploader instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->diskName = config('filesystems.default');
        $this->basePath = config('mediauploader.base_dir');
        $this->thumbDir = config('mediauploader.thumb_dir');
        $this->pathPermission = config('mediauploader.path_permission');
    }

    /**
     * Set disk
     *
     * @return void
     */
    public function disk($diskName)
    {
        $this->diskName = $diskName;

        return $this;
    }

    /**
     * Create Directory
     *
     * @param  string  $path
     * @return string
     */
    private function makeDir($path)
    {
        $realPath = $this->basePath.$path;

        if (! $this->exists($realPath)) {
            Storage::disk($this->diskName)->makeDirectory($realPath, $this->pathPermission);
        }

        return $realPath;
    }

    /**
     * Make File Name
     *
     * @param  string  $originalName
     * @param  string  $ext
     * @param  string|null  $name
     * @param  string  $realPath
     * @return string
     */
    private function makeFileName($originalName, $ext, $name, $realPath)
    {
        $timestamp = config('mediauploader.timestamp_prefix') == true ? '-'.time() : null;

        if ($name) {
            $fileName = Str::slug($name, '-').$timestamp;
        } elseif ($originalName) {
            $newName = str_replace('.'.$ext, '', $originalName);
            $fileName = Str::slug($newName, '-').$timestamp;
        } else {
            $fileName = uniqid();
        }

        if ($this->exists($realPath.'/'.$fileName)) {
            $fileName = $fileName.'-'.rand(1, 10);
        }

        return $fileName.'.'.$ext;
    }

    /**
     * Only thumb image create in "$definePath/thumb" folder
     *
     * @param  string  $path
     * @param  string  $file
     * @param  bool  $thumbPath
     * @param  int  $thumbWidth
     * @param  int  $thumbHeight
     * @return bool
     */
    public function thumb($path, $file, $thumbPath = false, $thumbWidth = 0, $thumbHeight = 0)
    {
        $realPath = $this->basePath.$path;
        $thumbWidth = $thumbWidth > 0 ? $thumbWidth : config('mediauploader.image_thumb_width');
        $thumbHeight = $thumbHeight > 0 ? $thumbHeight : config('mediauploader.image_thumb_height');

        //Path Create...
        $thumbPath = $thumbPath == true ? $path.'/'.$thumbPath : $path.'/'.$this->thumbDir;
        $thumbPath = $this->makeDir($thumbPath);

        $img = Image::make(Storage::disk($this->diskName)->path($realPath.'/'.$file));
        $img->resize($thumbWidth, $thumbHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        if ($thumbWidth && $thumbHeight) {
            $background = Image::canvas($thumbWidth, $thumbHeight);
            $background->insert($img, 'center');
            $background->save(Storage::disk($this->diskName)->path($thumbPath.'/'.$file));
        } else {
            $img->save(Storage::disk($this->diskName)->path($thumbPath.'/'.$file));
        }

        if (isset($img->filename)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Upload image ("$definePath" and "$definePath/thumb") folder
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $path
     * @param  bool  $thumb
     * @param  string|null  $name
     * @param  array  $imageResize
     * @param  array  $thumbResize
     * @return array
     */
    public function imageUpload($file, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        //File Name Generate...
        $fileName = $this->makeFileName($file->getClientOriginalName(), $file->getClientOriginalExtension(), $name, $realPath);

        $img = Image::make($file);

        //If real image need to resize...
        if (! empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(Storage::disk($this->diskName)->path($realPath.'/'.$fileName));

        if ($thumb) {
            $this->thumb($path, $fileName, false, $thumbResize[0], $thumbResize[1]);
        }

        $data['name'] = $fileName;
        $data['originalName'] = $file->getClientOriginalName();
        $data['size'] = $img->filesize();
        $data['width'] = $img->width();
        $data['height'] = $img->height();
        $data['mime_type'] = $img->mime();
        $data['ext'] = $file->getClientOriginalExtension();
        $data['url'] = $this->getUrl($realPath.'/'.$fileName);

        return $data;
    }

    /**
     * Upload any file type in "$definePath" folder
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $path
     * @param  string|null  $name
     * @return array
     */
    public function anyUpload($file, $path, $name = null)
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        //File Name Generate...
        $fileName = $this->makeFileName($file->getClientOriginalName(), $file->getClientOriginalExtension(), $name, $realPath);

        Storage::disk($this->diskName)->putFileAs($realPath, $file, $fileName);

        $data['name'] = $fileName;
        $data['originalName'] = $file->getClientOriginalName();
        $data['size'] = $file->getSize();
        $data['width'] = null;
        $data['height'] = null;
        $data['mime_type'] = $file->getMimeType();
        $data['ext'] = $file->getClientOriginalExtension();
        $data['url'] = $this->getUrl($realPath.'/'.$fileName);

        return $data;
    }

    /**
     * Upload base64 Image ("$definePath" and "$definePath/thumb") folder
     *
     * @param  string  $requestFile
     * @param  string  $path
     * @param  bool  $thumb
     * @param  string|null  $name
     * @param  array  $imageResize
     * @param  array  $thumbResize
     * @return array
     */
    public function base64ImageUpload($requestFile, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        $extension = explode('/', mime_content_type($requestFile))[1];

        //File Name Generate...
        $fileName = $this->makeFileName(null, $extension, $name, $realPath);

        $img = Image::make(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $requestFile)));
        $img->stream();

        //If real image need to resize...
        if (! empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(Storage::disk($this->diskName)->path($realPath.'/'.$fileName));

        if ($thumb) {
            $this->thumb($path, $fileName, false, $thumbResize[0], $thumbResize[1]);
        }

        $data['name'] = $fileName;
        $data['originalName'] = null;
        $data['size'] = $img->filesize();
        $data['width'] = $img->width();
        $data['height'] = $img->height();
        $data['mime_type'] = $img->mime();
        $data['ext'] = $extension;
        $data['url'] = $this->getUrl($realPath.'/'.$fileName);

        return $data;
    }

    /**
     * Upload Image from url ("$definePath" and "$definePath/thumb") folder
     *
     * @param  string  $requestFile
     * @param  string  $path
     * @param  bool  $thumb
     * @param  string|null  $name
     * @param  array  $imageResize
     * @param  array  $thumbResize
     * @return array
     */
    public function imageUploadFromUrl($requestFile, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        $extension = pathinfo($requestFile, PATHINFO_EXTENSION);
        $content = file_get_contents($requestFile);

        //File Name Generate...
        $fileName = $this->makeFileName(null, $extension, $name, $realPath);

        $img = Image::make($content);

        //If real image need to resize...
        if (! empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(Storage::disk($this->diskName)->path($realPath.'/'.$fileName));

        if ($thumb) {
            $this->thumb($path, $fileName, false, $thumbResize[0], $thumbResize[1]);
        }

        $data['name'] = $fileName;
        $data['originalName'] = null;
        $data['size'] = $img->filesize();
        $data['width'] = $img->width();
        $data['height'] = $img->height();
        $data['mime_type'] = $img->mime();
        $data['ext'] = $extension;
        $data['url'] = $this->getUrl($realPath.'/'.$fileName);

        return $data;
    }

    /**
     * Upload content to file in "$definePath" folder
     *
     * @param  string  $content
     * @param  string  $path
     * @param  string|null  $name
     * @param  string  $extension
     * @return array
     */
    public function contentUpload($content, $path, $name, $extension = 'jpg')
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        $fileName = $this->makeFileName(null, $extension, $name, $realPath);

        Storage::disk($this->diskName)->put($realPath.'/'.$fileName, $content);

        $data['name'] = $fileName;
        $data['originalName'] = null;
        $data['size'] = null;
        $data['width'] = null;
        $data['height'] = null;
        $data['mime_type'] = null;
        $data['ext'] = null;
        $data['url'] = $this->getUrl($realPath.'/'.$fileName);

        return $data;
    }

    /**
     * Upload & Converted Image to webp ("$definePath" and "$definePath/thumb") folder
     *
     * @param  string  $file
     * @param  string  $path
     * @param  bool  $thumb
     * @param  string|null  $name
     * @param  array  $imageResize
     * @param  array  $thumbResize
     * @return array
     */
    public function webpUpload($file, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        //File Name Generate...
        $fileName = $this->makeFileName($file->getClientOriginalName(), 'webp', $name, $realPath);

        $img = Image::make($file)->encode('webp', 70);

        //If real image need to resize...
        if (! empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(Storage::disk($this->diskName)->path($realPath.'/'.$fileName));

        if ($thumb) {
            $this->thumb($path, $fileName, false, $thumbResize[0], $thumbResize[1]);
        }

        $data['name'] = $fileName;
        $data['originalName'] = $file->getClientOriginalName();
        $data['size'] = $img->filesize();
        $data['width'] = $img->width();
        $data['height'] = $img->height();
        $data['mime_type'] = $img->mime();
        $data['ext'] = '.webp';
        $data['url'] = $this->getUrl($realPath.'/'.$fileName);

        return $data;
    }

    /**
     * Delete file "$definePath" folder
     *
     * @param  string  $path
     * @param  string  $file
     * @param  bool  $thumb
     * @return bool
     */
    public function delete($path, $file, $thumb = false)
    {
        $realPath = $this->basePath.$path;

        if ($this->exists($realPath.'/'.$file)) {
            Storage::disk($this->diskName)->delete($realPath.'/'.$file);

            if ($thumb) {
                Storage::disk($this->diskName)->delete($realPath.'/'.$this->thumbDir.'/'.$file);
            }

            return true;
        }

        return false;
    }

    /**
     * Delete directory "$definePath" folder
     *
     * @param  string  $path
     * @return bool
     */
    public function removeDirectory($path)
    {
        $realPath = $this->basePath.$path;

        if ($this->exists($realPath)) {
            try {
                Storage::disk($this->diskName)->deleteDirectory($realPath);
            } catch (\Exception $e) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * File exists checker
     *
     * @param  string  $path
     * @param  string  $name
     * @return bool
     */
    public function fileExists($path, $name)
    {
        $path = $this->basePath.$path;
        if ($name != null && $this->exists($path.'/'.$name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * File url generator
     *
     * @param  string  $path
     * @param  string  $name
     * @return string|null
     */
    public function showUrl($path, $name)
    {
        $path = $this->basePath.$path;

        if ($name != null && $this->exists($path.'/'.$name)) {
            return $this->getUrl($path.'/'.$name);
        } else {
            return null;
        }
    }

    /**
     * File link preview
     *
     * @param  string  $path
     * @param  string  $name
     * @return string|null
     */
    public function showFile($path, $name)
    {
        $path = $this->basePath.$path;

        if ($name && $this->exists($path.'/'.$name)) {
            return '<a href="'.$this->getUrl($path.'/'.$name).'" target="_blank">'.$name.'</a>';
        } else {
            return null;
        }
    }

    /**
     * Image preview
     *
     * @param  string  $path
     * @param  string  $name
     * @param  array|null  $array
     * @return string|null
     */
    public function showImg($path, $name, $array = null)
    {
        $path = $this->basePath.$path;

        $thumb = isset($array['thumb']) ? $this->thumbDir.'/' : '';
        $class = isset($array['class']) ? ' class="'.$array['class'].'"' : '';
        $id = isset($array['id']) ? ' id="'.$array['id'].'"' : '';
        $style = isset($array['style']) ? ' style="'.$array['style'].'"' : '';
        $alt = isset($array['alt']) ? ' alt="'.$array['alt'].'"' : '';
        $popup = isset($array['popup']) ? $array['popup'] : false;

        $imgSrc = null;
        $fakeImgView = false;
        if ($name && $this->exists($path.'/'.$thumb.$name)) {
            $imgSrc = $this->getUrl($path.'/'.$thumb.$name);
        } else {
            $fakeImgView = true;
            if (isset($array['fakeImg'])) {
                if (is_string($array['fakeImg'])) {
                    $imgSrc = $array['fakeImg'];
                } elseif (config('mediauploader.fake_image_url')) {
                    $imgSrc = config('mediauploader.fake_image_url');
                }
            }
        }

        if ($imgSrc) {
            $img = '<img src="'.$imgSrc.'"'.$alt.$class.$id.$style.'>';
            if ($popup === true && $fakeImgView === false) {
                return '<a href="'.$this->getUrl($path.'/'.$name).'" data-caption="'.$alt.' data-fancybox="group" data-fancybox" data-lyte-options="group:vacation">'.$img.'</a>';
            }

            return $img;
        } else {
            return null;
        }
    }

    /**
     * get the image or file url
     *
     * @return string
     */
    private function getUrl($path)
    {
        if ($this->diskName == 'local') {
            return route('mediauploader.preview', [$path]);
        }

        return Storage::disk($this->diskName)->url($path);
    }

    /**
     * return true or false based on file exist or not
     *
     * @return bool
     */
    private function exists($path)
    {
        return Storage::disk($this->diskName)->exists($path);
    }
}
