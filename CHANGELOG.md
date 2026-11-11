# Changelog

## 2.0.0 - Unreleased

### Breaking

- Requires PHP 8.2+ and Laravel 10–13 (Laravel 7–9 dropped).
- Uses `intervention/image` 3.x instead of the abandoned 2.x.
- Methods now have real parameter and return types. Passing e.g. `1` for `$thumb` still works (non-strict mode), but passing `null` for `$path` does not.
- Upload methods return `false` when the disk fails to write, and throw `InvalidArgumentException` for invalid input (unsupported image type, bad base64, `..` in a path, disallowed url).
- `webpUpload()` returns `'ext' => 'webp'` (was `'.webp'`), like every other method.
- `removeDirectory('')` / `removeDirectory('/')` now returns `false` instead of deleting the base directory / disk root.
- `delete($path, $file, true)` deletes the thumb even when the main file is already gone.
- `path_permission` config was removed. Laravel ignored it on all supported versions; set directory permissions in `config/filesystems.php` (`'permissions'` on the disk) instead.
- The preview route now only serves files inside `base_dir`, and accepts any path depth.
- `showImg()` with `'fakeImg' => false` no longer falls back to `fake_image_url`; pass `true`.

### Fixed

- **Existing files were silently overwritten.** The "file already exists" check looked for the name without its extension, so it never matched. Names now get `-1`, `-2`, ... until they are unique.
- `base_dir` is joined with a `/` (`uploads` + `images` → `uploads/images`, previously `uploadsimages`).
- `MediaUploader::disk('x')` only affects that call; it used to change the disk for every later call in the request.
- Popup markup in `showImg()` was invalid (`data-caption=" alt="...""`).
- Upper-case and missing client extensions (`PHOTO.JPG`, no extension) are normalised.
- `imageUploadFromUrl()` ignores query strings when detecting the extension and falls back to the detected mime type.
- `base64ImageUpload()` accepts raw base64 as well as data URIs and detects the type from the bytes.
- `thumb()` with a custom folder name used a folder called `1`.
- Url generation works for any disk name, not just `local`/`public`/`s3`.

### Security

- `imageUploadFromUrl()` only fetches `http`/`https`, refuses private / reserved IP ranges (including on redirects), and enforces a timeout and size limit (`remote.*` config).
- `showImg()`, `showFile()` and `showFileFromUrl()` escape all output; `javascript:` and other non-http urls are refused.
- Paths containing `.` or `..` segments are rejected.
- The preview route can be disabled or put behind middleware (`route.*` config) and sends `X-Content-Type-Options: nosniff`.

### Added

- `disk` config / `MEDIAUPLOADER_DISK` env to choose the package disk independently of `filesystems.default`.
- `random_name`, `image_driver` (`gd`/`imagick`), `image_quality`, `webp_quality`, `temporary_url_minutes` config.
- Config can be published with `--tag=mediauploader-config`.
- Test suite (Orchestra Testbench), PHPStan (Larastan), Pint, and GitHub Actions CI across PHP 8.2–8.4 and Laravel 10–13.

### Upgrading from 1.x

1. `composer require sudippalash/mediauploader:^2.0` (this also upgrades `intervention/image` to 3.x; if your app uses Intervention directly, see its [upgrade guide](https://image.intervention.io/v3/introduction/upgrade)).
2. If you published the config, compare it with the new `config/mediauploader.php` and add the new keys. Remove `path_permission`.
3. If you set `base_dir` with a trailing slash (`'uploads/'`), nothing changes. If you set it without one (`'uploads'`), 1.x stored files in `uploadsimages/...`; move those files, or keep the old layout by setting `base_dir` to `null` and prefixing your paths yourself.
4. If you serve private files through `/mediauploader/...`, set `base_dir` and consider `route.middleware`.
5. If you compared `$result['ext']` from `webpUpload()` with `'.webp'`, compare with `'webp'`.
