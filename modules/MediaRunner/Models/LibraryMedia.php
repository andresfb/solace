<?php

namespace Modules\MediaRunner\Models;

use Illuminate\Support\Str;
use Modules\MediaRunner\Libraries\MediaBasePath;

class LibraryMedia extends BaseMediaRunnerModel
{
    protected $table = 'media';

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function getFileInfo(): array
    {
        $mediaPath = app(MediaBasePath::class)->getBasePath($this->model_id, $this->id, $this->collection_name);

        $filePath = Str::of(config('media_runner.media_path'))
            ->append($mediaPath)
            ->append('/')
            ->append($this->file_name)
            ->toString();

        return [
            'original_name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_type' => $this->collection_name,
            'size_mb'   => round($this->size / 2048, 2),
            'file_path' => $filePath,
        ];
    }
}
