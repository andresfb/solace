<?php

namespace Modules\EmbyMediaRunner\Dtos;

final readonly class ProcessMediaItem
{
    /**
     * @param array<array<string, string>> $trailerUrls
     */
    public function __construct(
        public string $movieId,
        public string $name,
        public string $filePath,
        public array $trailerUrls,
    ) {}

    public function hasTrailerUrls(): bool
    {
        return ! empty($this->trailerUrls);
    }
}
