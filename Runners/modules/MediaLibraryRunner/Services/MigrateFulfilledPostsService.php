<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;

class MigrateFulfilledPostsService extends BaseSimpleMigrateService
{
    protected function getLibraryPosts(): Collection
    {
        return LibraryPost::query()
            ->tagged()
            ->withoutBanded()
            ->oldest()
            ->limit(
                config("$this->MIGRATE_FULFILLED.posts_limit")
            )
            ->get();
    }

    protected function getTaskName(): string
    {
        return $this->MIGRATE_FULFILLED;
    }

    protected function getErrorMessage(): string
    {
        return 'No Unpublished LibraryPosts found.';
    }
}
