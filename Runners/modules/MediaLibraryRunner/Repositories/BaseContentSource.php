<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Repositories;

use Modules\MediaLibraryRunner\Interfaces\ContentSourceInterface;
use Modules\MediaLibraryRunner\Models\Contents\ContentItem;
use Modules\MediaLibraryRunner\Models\Contents\ContentModel;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;

abstract class BaseContentSource implements ContentSourceInterface
{
    abstract public function getName(): string;

    abstract public function getTitle(ContentItem $content): string;

    abstract public function getModel(): ContentModel;

    abstract public function getContent(ContentItem $content): string;

    public function generateContent(LibraryPost $libraryPost, ContentItem $content): LibraryPost
    {
        $libraryPost->title = $this->getTitle($content);
        $libraryPost->content = $this->getContent($content);
        $libraryPost->source = "{$this->getName()}:$content->id";

        return $libraryPost;
    }

    public function getRandomContent(): ?ContentItem
    {
        return $this->getModel()::getRandom();
    }

    public function updateSource(int $sourceId): void
    {
        $this->getModel()::where('id', $sourceId)->update([
            'used' => true,
        ]);

        cache()->decrement(
            md5($this->getModel()::class.':count')
        );
    }
}
