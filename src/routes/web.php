<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/mediauploader/{path}/{path2?}/{path3?}/{path4?}', function ($path, $path2 = null, $path3 = null, $path4 = null) {
    $path = implode('/', array_filter([$path, $path2, $path3, $path4]));
    $filePath = Storage::disk('local')->path($path);
    
    if (! file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath);
})->name('mediauploader.preview');
