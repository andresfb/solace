<?php

namespace Modules\MediaRunner\Models\Item;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MediaRunner\Models\BaseMediaRunnerModel;
use Modules\MediaRunner\Models\Media\LibraryMedia;
use Modules\MediaRunner\Models\Media\Scopes\LibraryMediaScope;
use Modules\MediaRunner\Models\Post\LibraryPost;

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

    public function media(): HasMany|LibraryMedia
    {
        return $this->hasMany(LibraryMedia::class, 'model_id', 'id');
    }
}
