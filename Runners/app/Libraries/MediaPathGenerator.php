<?php

namespace App\Libraries;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class MediaPathGenerator implements PathGenerator
{
    /**
     * getPath Method.
     *
     * @param Media $media
     * @return string
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    /**
     * getPathForConversions Method.
     *
     * @param Media $media
     * @return string
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /**
     * getPathForResponsiveImages Method.
     *
     * @param Media $media
     * @return string
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /**
     * getBasePath Method.
     *
     * @param Media $media
     * @return string
     */
    private function getBasePath(Media $media): string
    {
        return  MediaBasePath::getBasePath(
            $media->model_id,
            $media->id,
            $media->collection_name
        );
    }
}
