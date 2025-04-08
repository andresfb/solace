<?php

namespace Modules\EmbyMediaRunner\Traits;

use FFMpeg\FFProbe;
use RuntimeException;

trait VideoDuration
{
    private function getVideoDuration(string $file): float
    {
        $ffProbe = FFProbe::create();

        $video = $ffProbe->streams($file)
            ->videos()
            ->first();

        if ($video === null){
            throw new RuntimeException('Invalid video file');
        }

        return (float) $video->get('duration', 0.00);
    }
}
