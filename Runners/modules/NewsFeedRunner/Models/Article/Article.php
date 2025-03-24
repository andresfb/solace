<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Article;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Common\Traits\TagsGettable;
use Modules\NewsFeedRunner\Models\Article\Scopes\ArticleMediaScope;
use Modules\NewsFeedRunner\Models\Feed\Feed;
use Modules\NewsFeedRunner\Models\Media\ArticleMedia;
use Modules\NewsFeedRunner\Models\NewsFeedRunnerModel;

/**
 * @property-read int $id
 * @property-read int $feed_id
 * @property-read string $hash
 * @property-read string $title
 * @property-read string $permalink
 * @property-read string|null $content
 * @property-read string|null $description
 * @property-read string|null $thumbnail
 * @property-read DateTimeInterface|null $read_at
 * @property-read CarbonImmutable|null $published_at
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 */
class Article extends NewsFeedRunnerModel
{
    use TagsGettable;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new ArticleMediaScope);
    }

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'published_at' => CarbonImmutable::class,
            'deleted_at' => CarbonImmutable::class,
            'created_at' => CarbonImmutable::class,
            'updated_at' => CarbonImmutable::class,
        ];
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ArticleMedia::class, 'model_id', 'id');
    }
}
