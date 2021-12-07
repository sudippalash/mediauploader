<?php

namespace Sudip\MediaUploder\Facades;

use Illuminate\Support\Facades\Facade;

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