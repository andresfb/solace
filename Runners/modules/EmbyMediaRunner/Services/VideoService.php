<?php

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VideoService
{
    public function exists(string $videoId, string $processPath): Collection
    {
        $search = "$processPath/*$videoId*";
        $files = collect(glob($search));

        if ($files->isEmpty()) {
            return $files;
        }

        return $files->map(function (string $file) {
            return trim($file);
        })
        ->reject(fn ($tag): bool => empty($tag));
    }

    public function isValid(string $file): bool
    {
        try {
            $probe = FFProbe::create();
            if (!$probe->isValid($file)) {
                return false;
            }

            $streams = $probe->streams($file);
            foreach ($streams->videos() as $video) {
                if (!$video->isVideo()) {
                    continue;
                }

                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error("Error validating $file:" . $e->getMessage());

            return false;
        }
    }
}
