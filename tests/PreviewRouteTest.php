<?php

namespace Sudip\MediaUploader\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sudip\MediaUploader\Facades\MediaUploader;

class PreviewRouteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('mediauploader.base_dir', 'uploads');
        $app['config']->set('mediauploader.route.enabled', true);
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

    public function test_local_disk_urls_use_the_preview_route(): void
    {
        $result = MediaUploader::disk('local')->anyUpload(UploadedFile::fake()->create('b.txt', 1), 'files');

        $this->assertSame(url('mediauploader/uploads/files/b.txt'), $result['url']);
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
