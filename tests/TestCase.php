<?php

namespace Sudip\MediaUploader\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sudip\MediaUploader\Facades\MediaUploader;
use Sudip\MediaUploader\Providers\AppServiceProvider;
use Sudip\MediaUploader\Providers\RouteServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [AppServiceProvider::class, RouteServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['MediaUploader' => MediaUploader::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('filesystems.default', 'public');
    }

    protected function setUp(): void
    {
        parent::setUp();

        MediaUploader::clearResolvedInstances();
    }
}
