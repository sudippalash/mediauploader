<?php

return [
    // Disk to store files on. null uses filesystems.default.
    'disk' => env('MEDIAUPLOADER_DISK'),

    // All files are stored inside this folder. Example: 'uploads'
    'base_dir' => null,

    // Thumbs are stored in this sub folder.
    'thumb_dir' => 'thumb',

    // Add a timestamp to every file name. Example: image-1658562981.png
    'timestamp_prefix' => false,

    // Use a random name instead of the original file name.
    'random_name' => false,

    // Image driver: gd or imagick
    'image_driver' => env('MEDIAUPLOADER_IMAGE_DRIVER', 'gd'),

    // Image quality (1-100)
    'image_quality' => 90,
    'webp_quality' => 70,

    // Default thumb size in px. Set one to null to keep the aspect ratio.
    'image_thumb_width' => 300,
    'image_thumb_height' => 300,

    // Image shown by showImg() when 'fakeImg' => true and the image is missing.
    // Example: 'images/fake.png' or 'https://example.com/images/fake.png'
    'fake_image_url' => null,

    // Return temporary urls (in minutes) on disks that support them, like private S3.
    'temporary_url_minutes' => null,

    // imageUploadFromUrl() settings
    'remote' => [
        'timeout' => 15,
        'max_size' => 10 * 1024 * 1024, // bytes
        'allow_private_hosts' => false,
    ],

    // Route that serves files from the private local disk: /mediauploader/{path}
    // Off by default: when on, set base_dir and add auth middleware, or anyone can read that disk.
    'route' => [
        'enabled' => false,
        'prefix' => 'mediauploader',
        'disk' => 'local',
        'middleware' => [], // Example: ['web', 'auth']
    ],
];
