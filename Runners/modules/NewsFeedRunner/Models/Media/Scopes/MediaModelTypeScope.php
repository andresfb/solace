<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Media\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MediaModelTypeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('model_type', 'App\Models\Article');
    }
}
