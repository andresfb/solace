<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class MigrateLostCauseNoBandedService extends BaseSimpleMigrateService
{
    protected function getLibraryPosts(): Collection
    {
        return LibraryPost::query()
            ->lostCause()
            ->withoutBanded()
            ->oldest()
            ->limit(
                config("$this->LO_NO_BANDED.posts_limit")
            )
            ->get();
    }

    protected function getTaskName(): string
    {
        return $this->LO_NO_BANDED;
    }

    protected function getErrorMessage(): string
    {
        return 'No Lost Cause without Banded Tags found';
    }
}
