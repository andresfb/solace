<?php

namespace Modules\MediaLibraryRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class MigrateUntaggedVideosService extends BaseSimpleMigrateService
{
    protected function getLibraryPosts(): Collection
    {
        return LibraryPost::query()
            ->untaggedVideos()
            ->withoutBanded()
            ->oldest()
            ->limit(
                config("$this->UNTAGGED_VIDEOS.posts_limit")
            )
            ->get();
    }

    protected function getTaskName(): string
    {
        return $this->UNTAGGED_VIDEOS;
    }

    protected function getErrorMessage(): string
    {
        return 'No Untagged LibraryPosts videos found';
    }
}
