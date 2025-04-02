<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Items\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LibraryItemScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('item');
    }
}
