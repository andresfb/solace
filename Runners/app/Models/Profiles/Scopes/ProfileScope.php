<?php

declare(strict_types=1);

namespace App\Models\Profiles\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProfileScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('profile');
    }
}
