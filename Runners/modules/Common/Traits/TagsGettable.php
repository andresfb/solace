<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait TagsGettable
{
    public function getTags(int $modelId, string $modelType, string $connection): Collection
    {
        $banded = array_merge(
            config('media_runner.banded_tags'),
            ['image', 'video']
        );

        return DB::connection($connection)
            ->table('tags')
            ->select('tags.name')
            ->join('taggables', 'tags.id', '=', 'taggables.tag_id')
            ->where('taggables.taggable_type', $modelType)
            ->where('taggables.taggable_id', $modelId)
            ->get()
            ->map(function ($tag) use ($banded) {
                $values = json_decode((string) $tag->name, true, 512, JSON_THROW_ON_ERROR);

                $keys = array_keys($values);
                if ($keys === []) {
                    return '';
                }

                $tag = str($values[$keys[0]])
                    ->trim()
                    ->lower();

                if ($tag->contains($banded)) {
                    return '';
                }

                // TODO: split tags like this Populism (Theory and Philosophy)

                return $tag->title()
                    ->replace(' ', '')
                    ->toString();
            })
            ->reject(fn ($tag): bool => empty($tag));
    }
}
