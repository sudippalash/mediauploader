<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get(config('mediauploader.route.prefix', 'mediauploader').'/{path}', function (string $path) {
    $baseDir = trim((string) config('mediauploader.base_dir'), '/');
    $segments = explode('/', $path);

    if (in_array('..', $segments) || in_array('.', $segments)) {
        abort(404);
    }

    // Only serve files inside base_dir.
    if ($baseDir && ! str_starts_with($path, $baseDir.'/')) {
        abort(404);
    }

    $disk = Storage::disk(config('mediauploader.route.disk', 'local'));
    $filePath = $disk->path($path);

    if (! is_file($filePath)) {
        abort(404);
    }

    return response()->file($filePath, ['X-Content-Type-Options' => 'nosniff']);
})
    ->where('path', '.*')
    ->middleware(config('mediauploader.route.middleware', []))
    ->name('mediauploader.preview');
