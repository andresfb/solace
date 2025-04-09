<?php

namespace Modules\EmbyMediaRunner\Traits;

use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

trait VideoDuration
{
    private function getVideoDuration(string $file): float
    {
        $this->line('Getting full video duration');

        $mediaReadDisk = 'media-read';

        $mediaReadPath = Storage::disk($mediaReadDisk)->path('');

        $fileLocation = str_replace($mediaReadPath, '', $file);

        return FFMpeg::fromDisk($mediaReadDisk)
            ->open($fileLocation)
            ->getDurationInSeconds();
    }
}
