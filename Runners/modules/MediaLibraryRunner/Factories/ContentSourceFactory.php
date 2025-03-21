<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Factories;

use Modules\MediaLibraryRunner\Interfaces\ContentSourceInterface;
use Modules\MediaLibraryRunner\Models\Content\ContentItem;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class ContentSourceFactory
{
    public static function loadContent(LibraryPost $post): ?LibraryPost
    {
        $contents = app('contents');

        foreach ($contents->shuffle() as $content) {
            $contentInstance = app($content);

            if (! $contentInstance instanceof ContentSourceInterface) {
                continue;
            }

            $contentItem = $contentInstance->getRandomContent();
            if (! $contentItem instanceof ContentItem) {
                continue;
            }

            return $contentInstance->generateContent($post, $contentItem);
        }

        return null;
    }

    public static function getContentSource(string $name): ?ContentSourceInterface
    {
        $contents = app('contents');

        foreach ($contents as $contentClass) {
            $contentInstance = app($contentClass);

            if (! $contentInstance instanceof ContentSourceInterface) {
                continue;
            }

            if ($contentInstance->getName() !== $name) {
                continue;
            }

            return $contentInstance;
        }

        return null;
    }
}
