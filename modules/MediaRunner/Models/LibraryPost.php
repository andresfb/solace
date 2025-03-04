<?php

namespace Modules\MediaRunner\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MediaRunner\Models\Scopes\LibraryItemsScope;

class LibraryPost extends BaseMediaRunnerModel
{
    protected $table = 'posts';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new LibraryItemsScope());
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'used' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LibraryItem::class, 'id', 'item_id');
    }
}
