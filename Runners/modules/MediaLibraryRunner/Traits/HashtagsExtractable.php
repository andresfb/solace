<?php

namespace Modules\MediaLibraryRunner\Traits;

trait HashtagsExtractable
{
    private function extractHashtags(string $text): array
    {
        // Use regex to find hashtags
        preg_match_all('/#\w+/', $text, $matches);

        // Extract hashtags into an array
        $hashtags = $matches[0];

        // Remove the '#' symbol from each hashtag
        return array_map(static fn ($hashtag): string => ltrim($hashtag, '#'), $hashtags);
    }
}
