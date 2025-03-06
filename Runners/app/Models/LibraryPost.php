<?php

namespace App\Models;

use App\Models\Scopes\LibraryItemsScope;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryPost extends BaseMediaRunnerModel
{
    protected $table = 'posts';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new LibraryItemsScope);
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
