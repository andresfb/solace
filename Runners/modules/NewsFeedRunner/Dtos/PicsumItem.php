<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Dtos;

class PicsumItem
{
    public function __construct(
        public int $id = 0,
        public string $author = '',
        public string $url = '',
        public string $downloadUrl = '',
        public string $imageUrl = '',
        public bool $found = false,
    ) {}

    public function getAttribution(): string
    {
        return "Picture Credit: [$this->author]($this->url)";
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            id: (int) $response['id'],
            author: $response['author'],
            url: $response['url'],
            downloadUrl: $response['download_url'],
            found: true,
        );
    }

    public static function empty(): self
    {
        return new self();
    }
}
