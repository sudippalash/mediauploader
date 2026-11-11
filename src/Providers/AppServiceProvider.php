<?php

namespace Sudip\MediaUploader\Providers;

use Illuminate\Support\ServiceProvider;
use Sudip\MediaUploader\MediaUploader;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/mediauploader.php', 'mediauploader');

        $this->app->bind(MediaUploader::class, fn () => new MediaUploader);
        $this->app->alias(MediaUploader::class, 'mediauploader');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/mediauploader.php' => config_path('mediauploader.php'),
            ], ['mediauploader-config', 'config']);
        }
    }
}
