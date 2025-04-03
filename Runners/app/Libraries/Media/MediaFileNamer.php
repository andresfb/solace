<?php

declare(strict_types=1);

namespace App\Libraries\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class MediaFileNamer extends DefaultFileNamer
{
    public function originalFileName(string $fileName): string
    {
        return hash('md5', sprintf('%s-%s', $fileName, time()));
    }

    public function extensionFromBaseImage(string $baseImage): string
    {
        return strtolower(pathinfo($baseImage, PATHINFO_EXTENSION));
    }

    public function temporaryFileName(Media $media, string $extension): string
    {
        return "{$this->responsiveFileName($media->file_name)}.".strtolower($extension);
    }
}
