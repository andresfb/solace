<?php

namespace Modules\EmbyMediaRunner\Services;

final class YouTubeService
{
    public function isYouTube(string $url): bool
    {
        return !blank($this->getYtVideoId($url));
    }

    public function getYtVideoId(string $url): string
    {
        // Extract the video ID from the URL
        preg_match("/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)|youtu\.be\/([a-zA-Z0-9_-]+)/", $url, $matches);

        if (empty($matches)) {
            return '';
        }

        if (!empty($matches[1])) {
            return $matches[1];
        }

        if (!empty($matches[2])) {
            return $matches[2];
        }

        return '';
    }

    public function cleanYoutubeUrl($url)
    {
        // Parse the URL into components
        $parsedUrl = parse_url($url);

        // If no query, return URL as is
        if (!isset($parsedUrl['query'])) {
            return $url;
        }

        // Parse the query string into an associative array
        parse_str($parsedUrl['query'], $queryParams);

        // Keep only the 'v' parameter (video ID)
        $cleanedQueryParams = array_intersect_key($queryParams, ['v' => '']);

        // Rebuild the query string
        $cleanedQuery = http_build_query($cleanedQueryParams);

        $baseQuery = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

        // Rebuild the URL
        return empty($cleanedQuery) ? $baseQuery : $baseQuery . '?' . $cleanedQuery;
    }
}
