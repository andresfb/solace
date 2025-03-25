<?php

namespace Modules\MediaLibraryRunner\Models\Content;

use Illuminate\Support\Facades\Cache;
use Modules\MediaLibraryRunner\Models\MediaRunnerModel;
use Random\RandomException;

/**
 * @method static where(...$args)
 */
abstract class ContentModel extends MediaRunnerModel
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
        ];
    }

    public static function getRandom(): ?ContentItem
    {
        $baseTake = 100;

        $totalJokes = Cache::remember(md5(static::class.':count'), now()->addDay(), static fn () => self::query()->where('used', 0)->count());

        $maxTakes = $totalJokes - $baseTake;
        if ($maxTakes <= 0) {
            $maxTakes = 200;
        }

        try {
            $randomOffset = random_int(0, max(0, (int) $maxTakes));
        } catch (RandomException) {
            return null;
        }

        $list = self::query()
            ->where('used', false)
            ->skip($randomOffset)
            ->take($baseTake)
            ->get();

        if ($list->isEmpty()) {
            return null;
        }

        return ContentItem::from($list->random());
    }
}
