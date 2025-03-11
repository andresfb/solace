<?php

namespace Modules\MediaLibraryRunner\Models\Post;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\MediaLibraryRunner\Models\BaseMediaRunnerModel;
use Modules\MediaLibraryRunner\Models\Item\LibraryItem;
use Modules\MediaLibraryRunner\Models\Item\Scopes\LibraryItemScope;
use Modules\MediaLibraryRunner\Models\Media\MediaItem;
use Modules\MediaLibraryRunner\Models\Post\Scopes\LibraryPostScope;

class LibraryPost extends BaseMediaRunnerModel
{
    protected $table = 'posts';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new LibraryPostScope);
        static::addGlobalScope(new LibraryItemScope);
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'used' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->BelongsTo(LibraryItem::class);
    }

    public function scopeTagged(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeUntaggedVideos(Builder $query): Builder
    {
        return $query->where('status', 0)
            ->where('type', 'video');
    }

    public function scopeWithoutBanded(Builder $query): Builder
    {
        return $query->whereNotIn('source', config('media_runner.banded_tags'));
    }

    public function getPostableInfo(): array
    {
        return [
            'libraryPostId' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'source' => $this->source,
            'mediaFiles' => $this->getMediaFiles(),
            'hashtags' => $this->getTags(),
        ];
    }

    /**
     * getMediaFiles Method.
     *
     * @return Collection<MediaItem>
     */
    public function getMediaFiles(): Collection
    {
        $files = collect();

        foreach ($this->item->media as $media) {
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

                $tag = str($values[$keys[0]])
                    ->trim()
                    ->lower()
                    ->slug()
                    ->toString();

                $banded = array_merge(
                    config('media_runner.banded_tags'),
                    ['image', 'video']
                );

                if (in_array($tag, $banded, true)) {
                    return '';
                }

                return $tag;
            })
            ->reject(fn ($tag): bool => empty($tag));
    }
}
