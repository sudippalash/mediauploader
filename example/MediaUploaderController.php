<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sudip\MediaUploder\Facades\MediaUploader;

class MediaUploaderController extends Controller
{
    public function index()
    {
        return view('mediauploader');
    }

    public function imageUpload(Request $request)
    {
        
        $image = MediaUploader::imageUpload($request->file, 'test', 1, null, [600, 600]);
        dd($image);
    }

    public function webpUpload(Request $request)
    {
        $image = MediaUploader::webpUpload($request->file, 'test', 1, null, [600, 600]);
        dd($image);
    }

    public function anyUpload(Request $request)
    {
        $image = MediaUploader::anyUpload($request->file, 'test');
        dd($image);
    }

    public function thumbUpload(Request $request)
    {
        $image = MediaUploader::thumb('test', $request->file);
        dd($image);
    }

    public function urlUpload(Request $request)
    {
        $image = MediaUploader::imageUploadFromUrl($request->file, 'test', 1, null, [600, 600]);
        dd($image);
    }

    public function baseUpload(Request $request)
    {
        $image = MediaUploader::base64ImageUpload($request->file, 'test', 1, null, [600, 600]);
        dd($image);
    }

    public function contentUpload(Request $request)
    {
        $image = MediaUploader::contentUpload($request->file, 'test', 'asdf.png');
        dd($image);
    }
}
