<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Interfaces;

use Modules\MediaLibraryRunner\Models\Content\BaseContentModel;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

interface ContentSourceInterface
{
    public function getName(): string;

    public function generateContent(LibraryPost $libraryPost, BaseContentModel $model): LibraryPost;

    public function updateSource(int $sourceId): void;

    // TODO: replace the BaseContentModel for a DTO that has all properties needed for all possible fields on all cnt tables and they are all optional
    public function getRandomContent(): ?BaseContentModel;
}
