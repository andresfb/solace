<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Post;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Modules\Common\Enum\LibraryPostStatus;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Traits\TagsGettable;
use Modules\MediaLibraryRunner\Models\Item\LibraryItem;
use Modules\MediaLibraryRunner\Models\Item\Scopes\LibraryItemScope;
use Modules\MediaLibraryRunner\Models\MediaRunnerModel;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

/**
 * @property int $id
 * @property int $item_id
 * @property string $type
 * @property string $slug
 * @property string $title
 * @property string $content
 * @property string|null $source
 * @property int $status
 * @property int $runner_status
 * @property bool $used
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read CarbonImmutable|null $created_at
 * @property-read CarbonImmutable|null $updated_at
 */
class LibraryPost extends MediaRunnerModel
{
    use ModuleConstants;
    use TagsGettable;

    protected $table = 'posts';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new LibraryItemScope);
    }

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'status' => LibraryPostStatus::class,
            'runner_status' => RunnerStatus::class,
            'deleted_at' => CarbonImmutable::class,
            'created_at' => CarbonImmutable::class,
            'updated_at' => CarbonImmutable::class,
        ];
    }

    public function item(): BelongsTo
    {
        return $this->BelongsTo(LibraryItem::class);
    }

    public function scopeTagged(Builder $query): Builder
    {
        return $query->where('status', LibraryPostStatus::TAGGED)
            ->where('runner_status', RunnerStatus::STASIS);
    }

    public function scopeUntaggedVideos(Builder $query): Builder
    {
        return $query->where('status', LibraryPostStatus::CREATED)
            ->where('runner_status', RunnerStatus::STASIS)
            ->where('type', 'video');
    }

    public function scopeWithoutBanded(Builder $query): Builder
    {
        return $query->whereNotIn('source', config('media_runner.banded_tags'));
    }

    public function scopeWithBanded(Builder $query): Builder
    {
        return $query->whereIn('source', config('media_runner.banded_tags'));
    }

    public function scopeUntaggedImages(Builder $query): Builder
    {
        return $query->where('status', LibraryPostStatus::CREATED)
            ->where('runner_status', RunnerStatus::STASIS)
            ->where('type', 'image');
    }

    public function scopeBandedReprocess(Builder $query): Builder
    {
        return $query->where('status', LibraryPostStatus::CREATED)
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('type', 'video')
                        ->whereIn('source', config('media_runner.banded_tags'))
                        ->where('runner_status', RunnerStatus::STASIS);
                })->orWhere('runner_status', RunnerStatus::REPROCESS);
            });
    }

    public function scopeLostCause(Builder $query): Builder
    {
        return $query->where('runner_status', RunnerStatus::LOST_CAUSE);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPostableInfo(string $taskName): array
    {
        return [
            'modelId' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'generator' => strtoupper(
                "POST=$this->id:ITEM=$this->item_id:TYPE:$this->type:LIST=$this->source:RUNNER=$this->MEDIA_LIBRARY"
            ),
            'source' => $this->source,
            'origin' => $this->MEDIA_LIBRARY,
            'fromAi' => false,
            'mediaFiles' => $this->getMediaFiles(),
            'tasker' => $taskName,
            'responses' => null,
            'hashtags' => $this->getTags(
                modelId: $this->id,
                modelType: 'App\Models\Post',
                connection: $this->getConnectionName() ?? config('database.media_runner_connection')
            ),
        ];
    }

    public function getMediaFiles(): Collection
    {
        $files = collect();

        foreach ($this->item?->media as $media) {
            $files->push($media->getFileInfo());
        }

        return $files;
    }
}
