<?php

namespace Sudip\MediaUploader\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Sudip\MediaUploader\Facades\MediaUploader;

class MediaUploaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_any_upload_stores_file_and_returns_metadata(): void
    {
        $result = MediaUploader::anyUpload(UploadedFile::fake()->create('Report Final.PDF', 12, 'application/pdf'), 'files');

        $this->assertSame('report-final.pdf', $result['name']);
        $this->assertSame('pdf', $result['ext']);
        $this->assertSame('Report Final.PDF', $result['originalName']);
        Storage::disk('public')->assertExists('files/report-final.pdf');
    }

    public function test_same_name_never_overwrites_existing_file(): void
    {
        $first = MediaUploader::anyUpload(UploadedFile::fake()->create('doc.txt', 1), 'files');
        $second = MediaUploader::anyUpload(UploadedFile::fake()->create('doc.txt', 1), 'files');
        $third = MediaUploader::anyUpload(UploadedFile::fake()->create('doc.txt', 1), 'files');

        $this->assertSame(['doc.txt', 'doc-1.txt', 'doc-2.txt'], [$first['name'], $second['name'], $third['name']]);
    }

    public function test_base_dir_is_joined_with_a_slash(): void
    {
        config(['mediauploader.base_dir' => 'uploads']);
        MediaUploader::clearResolvedInstances();

        MediaUploader::anyUpload(UploadedFile::fake()->create('a.txt', 1), 'images');

        Storage::disk('public')->assertExists('uploads/images/a.txt');
    }

    public function test_image_upload_resizes_and_creates_thumb(): void
    {
        $result = MediaUploader::imageUpload(UploadedFile::fake()->image('Photo.JPG', 1200, 800), 'images', true, null, [600, 400], [100, 100]);

        $this->assertSame('photo.jpg', $result['name']);
        $this->assertSame('jpg', $result['ext']);
        $this->assertSame([600, 400], [$result['width'], $result['height']]);
        $this->assertSame('image/jpeg', $result['mime_type']);
        Storage::disk('public')->assertExists('images/photo.jpg');
        Storage::disk('public')->assertExists('images/thumb/photo.jpg');

        $thumb = getimagesizefromstring(Storage::disk('public')->get('images/thumb/photo.jpg'));
        $this->assertSame([100, 100], [$thumb[0], $thumb[1]]);
    }

    public function test_webp_upload_converts_and_uses_plain_extension(): void
    {
        $result = MediaUploader::webpUpload(UploadedFile::fake()->image('pic.png', 300, 300), 'webps', true);

        $this->assertSame('pic.webp', $result['name']);
        $this->assertSame('webp', $result['ext']);
        $this->assertSame('image/webp', $result['mime_type']);
        Storage::disk('public')->assertExists('webps/thumb/pic.webp');
    }

    public function test_base64_upload_accepts_data_uri_and_raw_base64(): void
    {
        $png = base64_encode(UploadedFile::fake()->image('x.png', 10, 10)->getContent());

        $fromUri = MediaUploader::base64ImageUpload('data:image/png;base64,'.$png, 'b64', false, 'from uri');
        $fromRaw = MediaUploader::base64ImageUpload($png, 'b64', false, 'from raw');

        $this->assertSame('from-uri.png', $fromUri['name']);
        $this->assertSame('from-raw.png', $fromRaw['name']);
    }

    public function test_base64_upload_rejects_garbage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MediaUploader::base64ImageUpload('not base64 !!!', 'b64');
    }

    public function test_content_upload_reports_size_and_mime(): void
    {
        $result = MediaUploader::contentUpload('hello', 'contents', 'greeting', 'txt');

        $this->assertSame('greeting.txt', $result['name']);
        $this->assertSame(5, $result['size']);
        $this->assertSame('text/plain', $result['mime_type']);
    }

    public function test_thumb_for_existing_image_with_custom_folder(): void
    {
        MediaUploader::imageUpload(UploadedFile::fake()->image('a.png', 400, 200), 'images');

        $this->assertTrue(MediaUploader::thumb('images', 'a.png', 'small', 50, 0));
        $this->assertFalse(MediaUploader::thumb('images', 'missing.png'));

        // Height 0 falls back to the configured default (300): padded into a 50x300 box.
        $size = getimagesizefromstring(Storage::disk('public')->get('images/small/a.png'));
        $this->assertSame([50, 300], [$size[0], $size[1]]);

        // Height configured as null: scale by width only, keeping aspect ratio.
        config(['mediauploader.image_thumb_height' => null]);
        MediaUploader::thumb('images', 'a.png', 'small', 50);
        $size = getimagesizefromstring(Storage::disk('public')->get('images/small/a.png'));
        $this->assertSame([50, 25], [$size[0], $size[1]]);
    }

    public function test_delete_removes_file_and_thumb(): void
    {
        MediaUploader::imageUpload(UploadedFile::fake()->image('a.jpg'), 'images', true);

        $this->assertTrue(MediaUploader::delete('images', 'a.jpg', true));
        Storage::disk('public')->assertMissing('images/a.jpg');
        Storage::disk('public')->assertMissing('images/thumb/a.jpg');
        $this->assertFalse(MediaUploader::delete('images', 'a.jpg'));
    }

    public function test_remove_directory_refuses_empty_path(): void
    {
        MediaUploader::anyUpload(UploadedFile::fake()->create('a.txt', 1), 'keep');

        $this->assertFalse(MediaUploader::removeDirectory(''));
        $this->assertFalse(MediaUploader::removeDirectory('/'));
        Storage::disk('public')->assertExists('keep/a.txt');

        $this->assertTrue(MediaUploader::removeDirectory('keep'));
        Storage::disk('public')->assertMissing('keep/a.txt');
    }

    public function test_path_traversal_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MediaUploader::fileExists('../secrets', 'x');
    }

    public function test_disk_switch_does_not_leak_into_later_calls(): void
    {
        MediaUploader::disk('local')->anyUpload(UploadedFile::fake()->create('a.txt', 1), 'files');
        MediaUploader::anyUpload(UploadedFile::fake()->create('b.txt', 1), 'files');

        Storage::disk('local')->assertExists('files/a.txt');
        Storage::disk('public')->assertExists('files/b.txt');
        Storage::disk('local')->assertMissing('files/b.txt');
    }

    public function test_local_disk_urls_use_preview_route(): void
    {
        $result = MediaUploader::disk('local')->anyUpload(UploadedFile::fake()->create('a.txt', 1), 'files');

        $this->assertSame(url('mediauploader/files/a.txt'), $result['url']);
    }

    public function test_show_file_escapes_output(): void
    {
        Storage::disk('public')->put('files/x.txt', 'x');

        $html = MediaUploader::showFile('files', 'x.txt');
        $this->assertStringContainsString('>x.txt</a>', $html);

        $this->assertSame('none', MediaUploader::showFile('files', 'missing.txt', 'none'));
        $this->assertStringContainsString('&lt;b&gt;', MediaUploader::showFileFromUrl('https://example.com/a', '<b>'));
    }

    public function test_show_file_from_url_rejects_javascript_urls(): void
    {
        $this->assertSame('empty', MediaUploader::showFileFromUrl('javascript:alert(1)', 'x', 'empty'));
    }

    public function test_show_img_escapes_attributes_and_builds_valid_popup(): void
    {
        MediaUploader::imageUpload(UploadedFile::fake()->image('a.jpg'), 'images', true);

        $html = MediaUploader::showImg('images', 'a.jpg', [
            'thumb' => true,
            'popup' => true,
            'alt' => '"><script>',
            'class' => 'img-fluid',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('data-caption="&quot;&gt;&lt;script&gt;"', $html);
        $this->assertStringContainsString('/images/thumb/a.jpg', $html);
        $this->assertStringContainsString('class="img-fluid"', $html);
    }

    public function test_show_img_falls_back_to_fake_image(): void
    {
        config(['mediauploader.fake_image_url' => 'images/fake.png']);

        $this->assertNull(MediaUploader::showImg('images', 'missing.jpg'));
        $this->assertSame('<img src="'.url('images/fake.png').'">', MediaUploader::showImg('images', 'missing.jpg', ['fakeImg' => true]));
        $this->assertSame('<img src="custom.png">', MediaUploader::showImg('images', 'missing.jpg', ['fakeImg' => 'custom.png']));
    }

    public function test_url_upload_downloads_public_image(): void
    {
        $png = UploadedFile::fake()->image('x.png', 20, 10)->getContent();
        Http::fake(['*' => Http::response($png)]);

        $result = MediaUploader::imageUploadFromUrl('https://93.184.215.14/photo.png?w=200', 'images', false, 'remote');

        $this->assertSame('remote.png', $result['name']);
        $this->assertSame([20, 10], [$result['width'], $result['height']]);
    }

    public function test_url_upload_rejects_non_http_and_private_hosts(): void
    {
        foreach (['file:///etc/passwd', 'http://127.0.0.1/a.png', 'http://169.254.169.254/latest', 'http://[::1]/a.png'] as $url) {
            try {
                MediaUploader::imageUploadFromUrl($url, 'images');
                $this->fail("Expected {$url} to be rejected.");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
