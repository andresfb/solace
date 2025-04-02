<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Items;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\MediaLibraryRunner\Models\Media\LibraryMedia;
use Modules\MediaLibraryRunner\Models\Media\Scopes\LibraryMediaScope;
use Modules\MediaLibraryRunner\Models\MediaRunnerModel;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;

class LibraryItem extends MediaRunnerModel
{
    use SoftDeletes;

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
