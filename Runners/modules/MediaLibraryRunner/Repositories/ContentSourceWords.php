<?php

namespace Modules\MediaLibraryRunner\Repositories;

use Modules\MediaLibraryRunner\Models\Content\ContentModel;
use Modules\MediaLibraryRunner\Models\Content\ContentItem;
use Modules\MediaLibraryRunner\Models\Content\Word;

class ContentSourceWords extends BaseContentSource
{
    public function getName(): string
    {
        return 'word';
    }

    public function getModel(): ContentModel
    {
        return new Word();
    }

    public function getTitle(ContentItem $content): string
    {
        return 'Word Definition';
    }

    public function getContent(ContentItem $content): string
    {
        $word = sprintf(
            '**[%s](%s)**',
            ucwords(str_replace("_", " ", $content->word)),
            config('lost-cause.search_url') . urlencode(strtolower($content->word)),
        );

        return $word . "\n\n" . ucfirst($content->definition);
    }
}
