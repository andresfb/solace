<?php

namespace Modules\EmbyMediaRunner\Dtos;

final readonly class ProcessMediaItem
{
    public function __construct(
        public string $movieId,
        public string $name,
        public string $trailerUrl = '',
        public string $filePath = '',
    ) {}

    public function withTrailerUrl(string $trailerUrl): self
    {
        return new self(
            $this->movieId,
            $this->name,
            $trailerUrl,
            $this->filePath,
        );
    }

    public function withFilePath(string $filePath): self
    {
        return new self(
            $this->movieId,
            $this->name,
            $this->trailerUrl,
            $filePath,
        );
    }
}
