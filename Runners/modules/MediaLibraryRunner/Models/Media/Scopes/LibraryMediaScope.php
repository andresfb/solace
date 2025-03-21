<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Media\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LibraryMediaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('media');
    }
}
