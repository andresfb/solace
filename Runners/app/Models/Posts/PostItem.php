<?php

namespace App\Models\Posts;

use Illuminate\Support\Collection;
use Modules\MediaRunner\Models\Post\LibraryPost;

class PostItem
{
    private function __construct(
        public int $libraryPostId,
        public string $title,
        public string $content,
        public string $source,
        public Collection $mediaFiles,
        public Collection $hashtags,
    ) {}

    public static function createFromModel(LibraryPost $libraryPost): self
    {
        return new self(
            libraryPostId: $libraryPost->id,
            title: $libraryPost->title,
            content: $libraryPost->content,
            source: $libraryPost->source,
            mediaFiles: $libraryPost->getMediaFiles(),
            hashtags: $libraryPost->getTags(),
        );
    }

    public function getHash(): string
    {
        return md5("$this->libraryPostId|$this->title");
    }
}
