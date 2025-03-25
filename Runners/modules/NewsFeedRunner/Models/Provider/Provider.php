<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Provider;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\Builder;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Models\Feed\Feed;
use Modules\NewsFeedRunner\Models\NewsFeedRunnerModel;

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
        return $this->hasMany(Feed::class);
    }

    public function articles(): HasManyThrough
    {
        return $this->hasManyThrough(Article::class, Feed::class);
    }

    public function scopeWithImagedArticles(Builder $query): Builder
    {
        return $query->with(['articles' => function ($query): void {
            $query->where('published_at', '>=', now()->subDays($this->go_back_days))
                ->whereNotNull('thumbnail')
                ->orderBy('published_at');
        }]);
    }
}
