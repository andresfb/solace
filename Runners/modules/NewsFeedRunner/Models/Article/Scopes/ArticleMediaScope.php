<?php

namespace Modules\NewsFeedRunner\Models\Article\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ArticleMediaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('media');
    }
}
