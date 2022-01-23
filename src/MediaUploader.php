<?php

namespace Sudip\MediaUploder;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaUploader
{
    public $storageFolder, $basePath, $thumbDir;

    function __construct()
    {
        $this->storageFolder = 'storage/';
        $this->basePath = config('mediauploader.base_dir');
        $this->thumbDir = '/'.config('mediauploader.thumb_dir');
    }

    //Create Directory...
    private function makeDir($path)
    {
        $realPath = $this->basePath.$path;
        if (!Storage::exists($realPath)) {
            Storage::makeDirectory($realPath, 0777);
        }

        return $realPath;
    }

    //Make File Name...
    private function makeFileName($originalName, $ext, $name = null)
    {
        if ($name) {
            $fileName = Str::slug($name, '-').'.'.$ext;
        } elseif ($originalName) {
            $newName = str_replace('.'.$ext, '', $originalName);
            $fileName = Str::slug($newName, '-').'.'.$ext;
        } else {
            $fileName = uniqid().'.'.$ext;
        }

        return $fileName;
    }

    //Only thumb image create in "$definePath/thumb" folder....
    public function thumb($path, $file, $thumbPath = false, $thumbWidth = 0, $thumbHeight = 0)
    {
        $realPath = $this->basePath.$path;
        $thumbWidth = $thumbWidth > 0 ? $thumbWidth : config('mediauploader.image_thumb_width');
        $thumbHeight = $thumbHeight > 0 ? $thumbHeight : config('mediauploader.image_thumb_height');
        

        //Path Create...
        $thumbPath = $thumbPath == true ? $path.'/'.$thumbPath : $path.$this->thumbDir;
        $thumbPath = $this->makeDir($thumbPath);

        $img = Image::make($this->storageFolder.$realPath.'/'.$file);

        $img->resize($thumbWidth, $thumbHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $background = Image::canvas($thumbWidth, $thumbHeight);
        $background->insert($img, 'center');
        $background->save($this->storageFolder.$thumbPath.'/'.$file);

        if (isset($img->filename)) {
            return true;
        } else {
            return false;
        }
    }

    //Upload image ("$definePath" and "$definePath/thumb") folder....
    public function imageUpload($file, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        //File Name Generate...
        $fileName = $this->makeFileName($file->getClientOriginalName(), $file->getClientOriginalExtension(), $name);

        $img = Image::make($file);

        //If real image need to resize...
        if (!empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(public_path($this->storageFolder.$realPath.'/'.$fileName));

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
        $data['url'] = url($this->storageFolder.$realPath.'/'.$fileName);
        
        return $data;
    }

    //Upload any file type in "$definePath" folder....
    public function anyUpload($file, $path, $name = null)
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        //File Name Generate...
        $fileName = $this->makeFileName($file->getClientOriginalName(), $file->getClientOriginalExtension(), $name);

        Storage::putFileAs($realPath, $file, $fileName);
        
        $data['name'] = $fileName;
        $data['originalName'] = $file->getClientOriginalName();
        $data['size'] = $file->getSize();
        $data['width'] = null;
        $data['height'] = null;
        $data['mime_type'] = $file->getMimeType();
        $data['ext'] = $file->getClientOriginalExtension();
        $data['url'] = url($this->storageFolder.$realPath.'/'.$fileName);

        return $data;
    }

    // Upload base64 Image ("$definePath" and "$definePath/thumb") folder....
    public function base64ImageUpload($requestFile, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        $extension = explode('/', mime_content_type($requestFile))[1];

        //File Name Generate...
        $fileName = $this->makeFileName(null, $extension, $name);

        $img = Image::make(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $requestFile)));
        $img->stream();

        //If real image need to resize...
        if (!empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(public_path($this->storageFolder.$realPath.'/'.$fileName));

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
        $data['url'] = url($this->storageFolder.$realPath.'/'.$fileName);
        
        return $data;
    }

    // Upload Image from url ("$definePath" and "$definePath/thumb") folder....
    public function imageUploadFromUrl($requestFile, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        $extension = pathinfo($requestFile, PATHINFO_EXTENSION);
        $content = file_get_contents($requestFile);

        //File Name Generate...
        $fileName = $this->makeFileName(null, $extension, $name);

        $img = Image::make($content);

        //If real image need to resize...
        if (!empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(public_path($this->storageFolder.$realPath.'/'.$fileName));

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
        $data['url'] = url($this->storageFolder.$realPath.'/'.$fileName);
        
        return $data;
    }
    
    //Upload content to file in "$definePath" folder....
    public function contentUpload($content, $path, $name)
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        Storage::put($realPath.'/'.$name, $content);

        $data['name'] = $name;
        $data['originalName'] = null;
        $data['size'] = null;
        $data['width'] = null;
        $data['height'] = null;
        $data['mime_type'] = null;
        $data['ext'] = null;
        $data['url'] = url($this->storageFolder.$realPath.'/'.$name);

        return $data;
    }
    
    //Upload & Converted Image to webp ("$definePath" and "$definePath/thumb") folder....
    public function webpUpload($file, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [0, 0])
    {
        //Path Create...
        $realPath = $this->makeDir($path);

        //File Name Generate...
        $fileName = $this->makeFileName($file->getClientOriginalName(), 'webp', $name);

        $img = Image::make($file)->encode('webp', 70);

        //If real image need to resize...
        if (!empty($imageResize)) {
            $img->resize($imageResize[0], $imageResize[1]);
        }
        $img->save(public_path($this->storageFolder.$realPath.'/'.$fileName));

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
        $data['url'] = url($this->storageFolder.$realPath.'/'.$fileName);
        
        return $data;
    }

    //Delete file "$definePath" folder....
    public function delete($path, $file, $thumb = false)
    {
        $realPath = $this->basePath.$path;

        if (Storage::exists($realPath.'/'.$file)) {
            Storage::delete($realPath.'/'.$file);

            if ($thumb) {
                Storage::delete($realPath.'/thumb/'.$file);
            }
            return true;
        }
        return false;
    }

    //File link preview....
    public function showFile($path, $name)
    {
        $path = $this->storageFolder.$this->basePath.$path;
        
        if ($name != null && file_exists($path.'/'.$name)) {
            return '<a href="'.url('/'.$path.'/'.$name).'" target="_blank">'.$name.'</a>';
        } else {
            return '';
        }
    }

    //Image preview....
    public function showImg($path, $name, $array = null)
    {
        $path = $this->storageFolder.$this->basePath.$path;

        $thumb = (isset($array['thumb'])) ? $this->thumbDir : '';
        $class = (isset($array['class'])) ? ' class="'.$array['class'].'"' : '';
        $id = (isset($array['id'])) ? ' id="'.$array['id'].'"' : '';
        $style = (isset($array['style'])) ? ' style="'.$array['style'].'"' : '';
        $alt = (isset($array['alt'])) ? ' alt="'.$array['alt'].'"' : '';
        if ($name != '' && file_exists($path.'/'.$thumb.$name)) {
            return '<img src="'.url($path.'/'.$thumb.$name).'"'.$alt.$class.$id.$style.'>';
        } else {
            if (isset($array['fakeImg'])) {
                return '<img src="'.$array['fakeImg'].'"'.$alt.$class.$id.$style.'>';
            } else if (config('mediauploader.fake_image_url') != null) {
                return '<img src="'.config('mediauploader.fake_image_url').'"'.$alt.$class.$id.$style.'>';
            } else {
                return '';
            }
        }
    }
}
