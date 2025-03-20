<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Media;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Common\Libraries\MediaBasePath;
use Modules\MediaLibraryRunner\Models\BaseMediaRunnerModel;
use Modules\MediaLibraryRunner\Models\Item\LibraryItem;
use Modules\MediaLibraryRunner\Models\Media\Scopes\MediaModelTypeScope;

class LibraryMedia extends BaseMediaRunnerModel
{
    protected $table = 'media';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new MediaModelTypeScope);
    }

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'id', 'model_id');
    }

    public function getFileInfo(): MediaItem
    {
        $mediaPath = MediaBasePath::getBasePath($this->model_id, $this->id, $this->collection_name);

        $filePath = Str::of(config('media_runner.media_path'))
            ->append($mediaPath)
            ->append('/')
            ->append($this->file_name)
            ->toString();

        $data = $this->toArray();
        $data['filePath'] = $filePath;

        return MediaItem::from($data);
    }

    public function toArray(): array
    {
        return [
            'originalId' => $this->id,
            'originalName' => $this->name,
            'fileName' => $this->file_name,
            'mimeType' => $this->mime_type,
            'collectionName' => $this->collection_name,
            'fileSize' => $this->size,
        ];
    }
}
