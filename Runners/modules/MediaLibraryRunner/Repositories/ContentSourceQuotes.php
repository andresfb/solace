<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Repositories;

use Modules\MediaLibraryRunner\Models\Contents\ContentItem;
use Modules\MediaLibraryRunner\Models\Contents\ContentModel;
use Modules\MediaLibraryRunner\Models\Contents\Quote;

class ContentSourceQuotes extends BaseContentSource
{
    public function getName(): string
    {
        return 'quote';
    }

    public function getModel(): ContentModel
    {
        return new Quote;
    }

    public function getTitle(ContentItem $content): string
    {
        return ucfirst($content->category);
    }

    public function getContent(ContentItem $content): string
    {
        $author = sprintf(
            '*—[%s](%s)*',
            $content->author,
            config('lost-cause.search_url').urlencode($content->author)
        );

        return $content->quote."\n\n".$author;
    }
}
