<?php

namespace Sudip\MediaUploader\Providers;

use Sudip\MediaUploader\MediaUploader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/mediauploader.php', 'mediauploader');

        $this->app->bind('mediauploader', function () {
            return new MediaUploader;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/mediauploader.php' => config_path('mediauploader.php'),
        ], 'config');
    }
}
