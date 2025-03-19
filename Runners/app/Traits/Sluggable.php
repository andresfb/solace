<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Random\RandomException;

trait Sluggable
{
    /**
     * @throws RandomException
     */
    public function getSlug(): string
    {
        do {
            $slug = $this->genSlug();

            $exists = DB::connection($this->getConnectionName())
                ->table($this->getTable())
                ->where('slug', $slug)
                ->exists();

        } while ($exists);

        return $slug;
    }

    /**
     * @throws RandomException
     */
    private function genSlug(): string
    {
        $slug = Str::random(12);

        $addSlash = random_int(0, 1);

        if ($addSlash === 0) {
            return $slug;
        }

        // Remove the last character
        $dashSlug = substr($slug, 0, -1);

        // Get the length of the string
        $length = strlen($dashSlug);

        // Generate a random index between 1 and length - 2
        $randomIndex = random_int(1, $length - 2);

        // Insert the dash at the random index
        return substr($dashSlug, 0, $randomIndex) . '-' . substr($dashSlug, $randomIndex);
    }
}
