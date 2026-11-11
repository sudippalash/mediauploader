<?php

namespace Sudip\MediaUploader\Tests;

use Illuminate\Support\Facades\Storage;

class PreviewRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('mediauploader.base_dir', 'uploads');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::disk('local')->put('uploads/files/a.txt', 'hello');
        Storage::disk('local')->put('private/secret.txt', 'secret');
    }

    public function test_serves_files_inside_base_dir(): void
    {
        $this->get('/mediauploader/uploads/files/a.txt')->assertOk();
    }

    public function test_refuses_files_outside_base_dir(): void
    {
        $this->get('/mediauploader/private/secret.txt')->assertNotFound();
    }

    public function test_refuses_traversal_and_missing_files(): void
    {
        $this->get('/mediauploader/uploads/../private/secret.txt')->assertNotFound();
        $this->get('/mediauploader/uploads/%2E%2E/private/secret.txt')->assertNotFound();
        $this->get('/mediauploader/uploads/files/missing.txt')->assertNotFound();
        $this->get('/mediauploader/uploads/files')->assertNotFound();
    }
}
