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

    public function getRandomContent(): ?BaseContentModel;
}
