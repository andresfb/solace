<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Provider\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FeedsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('feeds');
    }
}
