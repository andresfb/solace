<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Item;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MediaLibraryRunner\Models\BaseMediaRunnerModel;
use Modules\MediaLibraryRunner\Models\Media\LibraryMedia;
use Modules\MediaLibraryRunner\Models\Media\Scopes\LibraryMediaScope;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class LibraryItem extends BaseMediaRunnerModel
{
    protected $table = 'items';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new LibraryMediaScope);
    }

    protected function casts(): array
    {
        return [
            'active' => 'bool',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(LibraryPost::class, 'id', 'item_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(LibraryMedia::class, 'model_id', 'id');
    }
}
