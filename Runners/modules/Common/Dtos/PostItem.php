<?php

namespace Modules\Common\Dtos;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PostItem extends Data
{
    private string $hash;

    public function __construct(
        public int $libraryPostId,
        public string $title,
        public string $content,
        public string $source,
        public string $origin,
        public Collection $mediaFiles,
        public Collection $hashtags,
    ) {}

    public function getHash(): string
    {
        if (! isset($this->hash) || ($this->hash === '' || $this->hash === '0')) {
            $this->hash = md5("$this->libraryPostId|$this->title");
        }

        return $this->hash;
    }
}
