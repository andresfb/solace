<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Post;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Common\Enum\LibraryPostStatus;
use Modules\Common\Enum\RunnerStatus;
use Modules\MediaLibraryRunner\Models\BaseMediaRunnerModel;
use Modules\MediaLibraryRunner\Models\Item\LibraryItem;
use Modules\MediaLibraryRunner\Models\Item\Scopes\LibraryItemScope;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

/**
 * @property string $title
 * @property integer $item_id
 * @property string $source
 */
class LibraryPost extends BaseMediaRunnerModel
{
    use ModuleConstants;

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
                        ->whereIn('source', config('media_runner.banded_tags'));
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
    public function getPostableInfo(): array
    {
        return [
            'libraryPostId' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'generator' => strtoupper(
                "POST=$this->id:ITEM=$this->item_id:LIST=$this->source:RUNNER=$this->MEDIA_LIBRARY"
            ),
            'source' => $this->source,
            'origin' => $this->MEDIA_LIBRARY,
            'fromAi' => false,
            'mediaFiles' => $this->getMediaFiles(),
            'hashtags' => $this->getTags(),
            'responses' => null,
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

    public function getTags(): Collection
    {
        return DB::connection(config('database.media_runner_connection'))
            ->table('tags')
            ->select('tags.name')
            ->join('taggables', 'tags.id', '=', 'taggables.tag_id')
            ->where('taggables.taggable_type', 'App\Models\Post')
            ->where('taggables.taggable_id', $this->id)
            ->get()
            ->map(function ($tag) {
                $values = json_decode((string) $tag->name, true, 512, JSON_THROW_ON_ERROR);

                $keys = array_keys($values);
                if ($keys === []) {
                    return '';
                }

                $banded = array_merge(
                    config('media_runner.banded_tags'),
                    ['image', 'video']
                );

                $tag = str($values[$keys[0]])
                    ->trim()
                    ->lower();

                if ($tag->contains($banded)) {
                    return '';
                }

                return $tag->title()
                    ->replace(' ', '')
                    ->toString();
            })
            ->reject(fn ($tag): bool => empty($tag));
    }
}
