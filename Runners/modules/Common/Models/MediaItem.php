<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Spatie\LaravelData\Data;

class MediaItem extends Data
{
    public function __construct(
        public int $originalId,
        public string $originalName,
        public string $fileName,
        public string $mimeType,
        public string $collectionName,
        public int $fileSize,
        public string $filePath,
    ) {}

    public static function loadEmpty(): static
    {
        return self::from([
            'originalId' => 0,
            'originalName' => '',
            'fileName' => '',
            'mimeType' => '',
            'collectionName' => '',
            'fileSize' => 0,
            'filePath' => '',
        ]);
    }
}
