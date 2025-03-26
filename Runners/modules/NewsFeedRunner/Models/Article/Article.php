<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Article;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
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
        $providerName = str($this->feed->provider->name ?? '')
            ->replace(' ', '_')
            ->trim()
            ->value();

        $feedName = str($this->feed->title ?? '')
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
        foreach ($this->media as $media) {
            $files->push($media->getFileInfo());
        }

        return $files;
    }

    private function parseContent(): string
    {
        // Helper function to clean text
        $cleanText = static function (string $text): Stringable {
            return str($text)
                ->replaceMatches('/\s{2,}/', ' ')    // Replace multiple spaces with a single space
                ->replaceMatches('/\n{2,}/', "\n")   // Replace multiple newlines with a single newline
                ->replace("\t", ' ')                 // Replace tabs with spaces
                ->trim();                            // Remove leading/trailing whitespace
        };

        // Clean and prepare both strings
        $content = $cleanText($this->content ?? '');
        $description = $cleanText($this->description ?? '');

        $lowerContent = $content->lower();
        $lowerDescription = $description->lower();

        // Handle empty cases
        if ($lowerContent->isEmpty()) {
            return $description->value();
        }

        if ($lowerDescription->isEmpty()) {
            return $content->value();
        }

        // Handle identical content
        if ($lowerContent === $lowerDescription) {
            return $content->value();
        }

        // Handle truncated content
        if ($lowerContent->endsWith(['...', '[...]'])) {
            return $description->value();
        }

        if ($lowerDescription->endsWith(['...', '[...]'])) {
            return $content->value();
        }

        // Check if they start with the same text (first 100 chars)
        $startsWithSameText = $lowerContent->substr(0, 100) === $lowerDescription->substr(0, 100);

        if ($startsWithSameText) {
            // Return the longer version when both start the same
            return ($lowerContent->length() > $lowerDescription->length())
                ? $content->value()
                : $description->value();
        }

        // Combine content based on length
        if ($lowerContent->length() < $lowerDescription->length()) {
            return "{$content->value()}\n\n{$description->value()}";
        }

        return "{$description->value()}\n\n{$content->value()}";
    }
}
