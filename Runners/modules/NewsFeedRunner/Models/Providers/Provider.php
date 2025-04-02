<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\NewsFeedRunner\Models\Feeds\Feed;
use Modules\NewsFeedRunner\Models\NewsFeedRunnerModel;
use Modules\NewsFeedRunner\Models\Providers\Scopes\FeedsScope;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $description
 * @property-read string|null $home_page
 * @property-read bool $status
 * @property-read int $go_back_days
 * @property-read int $order
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 */
class Provider extends NewsFeedRunnerModel
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new FeedsScope);
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'go_back_days' => 'integer',
            'order' => 'integer',
            'deleted_at' => CarbonImmutable::class,
            'created_at' => CarbonImmutable::class,
            'updated_at' => CarbonImmutable::class,
        ];
    }

    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class)
            ->where('status', true)
            ->orderBy('order');
    }

    public function scopeActiveWithFeeds(Builder $query): Builder
    {
        return $query->where('status', true)
            ->orderBy('order');
    }
}
