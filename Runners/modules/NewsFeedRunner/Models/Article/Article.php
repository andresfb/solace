<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Article;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\Common\Traits\TagsGettable;
use Modules\NewsFeedRunner\Models\Article\Scopes\ArticleMediaScope;
use Modules\NewsFeedRunner\Models\Feed\Feed;
use Modules\NewsFeedRunner\Models\Media\ArticleMedia;
use Modules\NewsFeedRunner\Models\NewsFeedRunnerModel;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

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
    use ModuleConstants;
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

    /**
     * @return array<string, mixed>
     */
    public function getPostableInfo(string $taskName): array
    {
        $providerName = str($this->feed->provider->name)
            ->replace(' ', '_')
            ->trim()
            ->value();

        $feedName = str($this->feed->tile)
            ->replace(' ', '_')
            ->trim()
            ->value();

        return [
            'modelId' => $this->id,
            'title' => $this->title,
            'content' => $this->parseContent(),
            'generator' => strtoupper(
                "ARTICLE=$this->id:PROVIDER=$providerName:FEED:$feedName:RUNNER=$this->NEWS_FEED"
            ),
            'source' => $providerName,
            'origin' => $this->NEWS_FEED,
            'fromAi' => false,
            'mediaFiles' => $this->getMediaFiles(),
            'tasker' => $taskName,
            'responses' => null,
            'hashtags' => $this->getTags(
                modelId: $this->id,
                modelType: 'App\Models\Item',
                connection: $this->getConnectionName() ?? config('database.news_feed_runner_connection')
            ),
        ];
    }

    public function getMediaFiles(): Collection
    {
        $files = collect();

        /** @var ArticleMedia $media */
        foreach ($this->item?->media as $media) {
            $files->push($media->getFileInfo());
        }

        return $files;
    }

    private function parseContent(): string
    {
        $content = str($this->content ?? '')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->replace("\n\n\n\n", "\n")
            ->replace("\n\n\n", "\n")
            ->replace("\n\n", "\n")
            ->replace("\t", ' ')
            ->trim();

        $description = str($this->description ?? '')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->replace("\n\n\n\n", "\n")
            ->replace("\n\n\n", "\n")
            ->replace("\n\n", "\n")
            ->replace("\t", ' ')
            ->trim();

        $lowerContent = $content->lower();
        $lowerDescription = $description->lower();

        if ($lowerContent->isEmpty()) {
            return $description->value();
        }

        if ($lowerDescription->isEmpty()) {
            return $content->value();
        }

        if ($lowerContent === $lowerDescription) {
            return $content->value();
        }

        if ($lowerContent->endsWith(['...', '[...]'])) {
            return $description->value();
        }

        if ($lowerDescription->endsWith(['...', '[...]'])) {
            return $content->value();
        }

        $startsContent = $lowerContent->substr(0, 100);
        $startsDescription = $lowerDescription->substr(0, 100);

        if ($startsContent === $startsDescription) {
            if ($lowerContent->length() > $lowerDescription->length()) {
                return $content->value();
            }

            return $description->value();
        }

        if ($lowerContent->length() < $lowerDescription->length()) {
            return $content->append("\n\n")
                ->append($description->value())
                ->value();
        }

        return $description->append("\n\n")
            ->append($content->value())
            ->value();
    }
}
