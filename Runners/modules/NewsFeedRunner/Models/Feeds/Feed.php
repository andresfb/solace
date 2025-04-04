<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Feeds;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Models\NewsFeedRunnerModel;
use Modules\NewsFeedRunner\Models\Providers\Provider;

/**
 * @property-read int $id
 * @property-read int $provider_id
 * @property-read string $title
 * @property-read string $url
 * @property-read string|null $logo
 * @property-read bool $status
 * @property-read int $order
 * @property-read Provider|null $provider
 * @property-read DateTimeInterface|null $refreshed_at
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 */
class Feed extends NewsFeedRunnerModel
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'order' => 'integer',
            'refreshed_at' => 'datetime',
            'deleted_at' => CarbonImmutable::class,
            'created_at' => CarbonImmutable::class,
            'updated_at' => CarbonImmutable::class,
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function scopeQuoteBased(Builder $query): Builder
    {
        return $query->whereIn('id', Config::array('news_feed_runner.quote-based-feeds'))
            ->whereIn('provider_id', Config::array('news_feed_runner.quote-based-providers'));
    }
}
