<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Sluggable
{
    public function getSlug(): string
    {
        do {
            $slug = Str::random(11);

            $exists = DB::connection($this->getConnection())
                ->table($this->getTable())
                ->where('slug', $slug)
                ->exists();

        } while ($exists);

        return $slug;
    }
}
