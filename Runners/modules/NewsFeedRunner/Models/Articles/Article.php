<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Articles;

use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Stringable;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Traits\TagsGettable;
use Modules\NewsFeedRunner\Models\Articles\Scopes\ArticleMediaScope;
use Modules\NewsFeedRunner\Models\Feeds\Feed;
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
 * @property-read string|null $attribution
 * @property-read int $runner_status
 * @property-read DateTime|null $read_at
 * @property-read DateTime|null $published_at
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 */
class Article extends NewsFeedRunnerModel
{
    use ModuleConstants;
    use TagsGettable;

    protected $guarded = ['id'];

    private array $quoteBasedFeeds;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->quoteBasedFeeds = Config::array('news_feed_runner.quote-based-feeds');
    }

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new ArticleMediaScope);
    }

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'published_at' => 'datetime',
            'runner_status' => RunnerStatus::class,
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

    public function scopeWithoutQuoteBased(Builder $query): Builder
    {
        return $query->whereNotIn(
            'feed_id',
            Config::array('news_feed_runner.quote-based-feeds')
        );
    }

    public function scopeWithQuoteBased(Builder $query): Builder
    {
        return $query->whereIn(
            'feed_id',
            Config::array('news_feed_runner.quote-based-feeds')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getPostableInfo(string $taskName): array
    {
        // TODO: change this function to return a PostItem class

        $providerName = str($this->feed->provider->name ?? '')
            ->replace(' ', '_')
            ->trim()
            ->value();

        $feedName = str($this->feed->title ?? '')
            ->replace(' ', '_')
            ->trim()
            ->value();

        $mediaFiles = $this->getMediaFiles();

        return [
            'modelId' => $this->id,
            'identifier' => $this->permalink,
            'title' => $this->title,
            'content' => str(
                nl2br($this->addLinkDateInfo($this->parseContent()))
            )
                ->replace('<br /><br /><br /><br />', '<br /><br />')
                ->value(),
            'generator' => strtoupper(
                "ARTICLE=$this->id:PROVIDER=$providerName:FEED:$feedName:RUNNER=$this->NEWS_FEED:TASK=$taskName"
            ),
            'source' => $this->isQuoteBased() ? 'quote' : $providerName,
            'origin' => $this->NEWS_FEED,
            'tasker' => $taskName,
            'image' => $mediaFiles->isEmpty() ? $this->thumbnail : '',
            'attribution' => $this->attribution ?? '',
            'fromAi' => $taskName === $this->IMPORT_AI_ARTICLE,
            'priority' => $this->feed?->provider?->order ?? 100,
            'responses' => null,
            'mediaFiles' => $mediaFiles,
            'hashtags' => $this->getTags(
                modelId: $this->id,
                modelType: 'App\Models\Article',
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

    public function isQuoteBased(): bool
    {
        return in_array($this->feed_id, $this->quoteBasedFeeds, true);
    }

    public function parseContent(): string
    {
        // Helper function to clean text
        $cleanText = static function (string $text): Stringable {
            return str($text)
                ->replaceMatches('/\s{2,}/', ' ')        // Replace multiple spaces with a single space
                ->replaceMatches('/\n{2,}/', '<br />')   // Replace multiple newlines with a single newline
                ->replace("\t", ' ')                     // Replace tabs with spaces
                ->replace("\r", '')
                ->replace(
                    '<br /><br /><br /><br />', '<br /><br />'
                )                                        // Replace multiple br with two br
                ->trim();                                // Remove leading/trailing whitespace
        };

        $content = $cleanText($this->content ?? '');
        $description = $cleanText($this->description ?? '');

        if ($this->isQuoteBased()) {
            return $this->prepareQuoteContent($content);
        }

        $lowerContent = $content->lower();
        $lowerDescription = $description->lower();

        if ($lowerContent->isEmpty()) {
            return $description->value();
        }

        if ($lowerDescription->isEmpty()) {
            return $content->value();
        }

        // Handle identical content
        if ($lowerContent->exactly($lowerDescription)) {
            return $content->value();
        }

        // Handle truncated content
        if ($lowerContent->endsWith(['...', '[...]'])) {
            return $description->value();
        }

        if ($lowerDescription->endsWith(['...', '[...]'])) {
            return $content->value();
        }

        // Check if they start with the same text (first 65 chars)
        $startsWithSameText = $lowerContent->substr(0, 65) === $lowerDescription->substr(0, 65);

        if ($startsWithSameText) {
            // Return the longer version when both start the same
            return ($lowerContent->length() > $lowerDescription->length())
                ? $content->value()
                : $description->value();
        }

        // Combine content based on length
        if ($lowerContent->length() < $lowerDescription->length()) {
            return "{$content->value()}<br /><br />{$description->value()}";
        }

        return "{$description->value()}<br /><br />{$content->value()}";
    }

    private function addLinkDateInfo(string $content): string
    {
        $linked = str($content)
            ->trim()
            ->append('<br /><br />')
            ->append("[source]($this->permalink)")
            ->append('<br />');

        if ($this->isQuoteBased()) {
            return $linked->value();
        }

        return $linked->trim()
            ->append(sprintf(
                '<small><em>Published: %s</em></small>',
                CarbonImmutable::parse($this->published_at)->format('D, M j, Y')
            ))
            ->append('<br />')
            ->value();
    }

    private function prepareQuoteContent(Stringable $content): string
    {
        return $content->trim()
            ->append('<br /><br />')
            ->append('— ')
            ->append($this->title)
            ->value();
    }
}
