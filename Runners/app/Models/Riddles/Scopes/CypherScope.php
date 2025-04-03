<?php

declare(strict_types=1);

namespace App\Models\Riddles\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CypherScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->with('cypher');
    }
}
