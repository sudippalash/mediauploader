<?php


namespace Sudip\MediaUploader;

use Illuminate\Support\ServiceProvider;

class MediaUploaderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mediauploader.php', 'mediauploader');

        $this->app->bind('mediauploader', function () {
            return new MediaUploader();
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
            __DIR__ . '/../config/mediauploader.php' => config_path('mediauploader.php'),
        ], 'mediauploader');
    }
}
