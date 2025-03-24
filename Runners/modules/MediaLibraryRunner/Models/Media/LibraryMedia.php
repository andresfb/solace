<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Media;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Common\Models\Media;
use Modules\MediaLibraryRunner\Models\Item\LibraryItem;
use Modules\MediaLibraryRunner\Models\Media\Scopes\MediaModelTypeScope;

class LibraryMedia extends Media
{
    use SoftDeletes;

    protected $table = 'media';

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.media_runner_connection'));
    }

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new MediaModelTypeScope);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'id', 'model_id');
    }

    protected function getMediaPath(): string
    {
        return config('media_runner.media_path');
    }
}
