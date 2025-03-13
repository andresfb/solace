<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Media;

use Illuminate\Contracts\Support\Arrayable;

class MediaItem implements Arrayable
{
    public function __construct(
        public int $originalId,
        public string $originalName,
        public string $fileName,
        public string $mimeType,
        public string $collectionName,
        public string $fileSize,
        public string $filePath,
    ) {}

    public function toArray(): array
    {
        return [
            'original_id' => $this->originalId,
            'original_name' => $this->originalName,
            'file_name' => $this->fileName,
            'mime_type' => $this->mimeType,
            'size' => $this->fileSize,
            'file_path' => $this->filePath,
            'collection_name' => $this->collectionName,
        ];
    }
}
