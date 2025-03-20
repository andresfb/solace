<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Content;

use Illuminate\Support\Facades\Cache;
use Random\RandomException;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $category
 */
class Joke extends BaseContentModel
{
    public $timestamps = false;

    protected $table = 'cnt_jokes';

    public static function getRandom(): ?self
    {
        $baseTake = 100;

        $totalJokes = Cache::remember('jokes:count', now()->addDays(10), static fn() => self::query()->where('used', 0)->count());

        try {
            $randomOffset = random_int(0, max(0, $totalJokes - $baseTake));
        } catch (RandomException) {
            return null;
        }

        $list = self::query()
            ->where('used', 0)
            ->skip($randomOffset)
            ->take($baseTake)
            ->get();

        if ($list->isEmpty()) {
            return null;
        }

        return $list->random();
    }
}
