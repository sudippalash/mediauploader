<?php

namespace Sudip\MediaUploder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class MediaUploader.
 *
 * @see \Sudip\MediaUploder\MediaUploader
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
