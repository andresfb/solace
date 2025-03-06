<?php

namespace App\Models;

use App\Models\Scopes\LibraryMediaScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
