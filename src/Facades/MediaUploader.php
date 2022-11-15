<?php

namespace Sudip\MediaUploader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class MediaUploader.
 *
 * @see \Sudip\MediaUploader\MediaUploader
 */
class MediaUploader extends Facade
{
    /**
     * Return facade accessor
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mediauploader';
    }
}
