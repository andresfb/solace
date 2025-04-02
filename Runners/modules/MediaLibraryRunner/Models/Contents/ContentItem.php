<?php

namespace Modules\MediaLibraryRunner\Models\Contents;

use Spatie\LaravelData\Data;

class ContentItem extends Data
{
    public function __construct(
        public int $id,
        public string $category = '',
        public string $title = '',
        public string $body = '',
        public string $quote = '',
        public string $author = '',
        public string $word = '',
        public string $definition = '',
    ) {}
}
