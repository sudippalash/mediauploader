<?php

namespace Sudip\MediaUploader\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $router = $this->app->make(Router::class);
    }
}
