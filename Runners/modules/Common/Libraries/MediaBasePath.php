<?php

declare(strict_types=1);

namespace Modules\Common\Libraries;

use Illuminate\Support\Str;

class MediaBasePath
{
    public static function getBasePath(int $itemId, int $mediaId, string $collectionName): string
    {
        $contentId = str_pad((string) $itemId, 12, '0', STR_PAD_LEFT);

        return Str::of(
            collect(str_split($contentId, 3))
                ->reverse()
                ->implode('/')
        )
            ->append('/')
            ->append($collectionName)
            ->append('/')
            ->append((string) $mediaId)
            ->toString();
    }
}
