<?php

namespace Modules\MediaLibraryRunner\Models\Post\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LibraryPostScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('status', '<', 2);
    }
}
