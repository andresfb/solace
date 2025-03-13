<?php

declare(strict_types=1);

namespace App\Models\Hashtags\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HashtagsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('hashtags');
    }
}
