<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Dtos\PostItem;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImagedArticleService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function execute(Article $article): void
    {
        $this->line('Loading the Media Files and tags...');

        PostSelectedEvent::dispatch(
            PostItem::from(
                $article->load('feed.provider')
                    ->getPostableInfo($this->IMPORT_IMAGED_ARTICLES)
            ),
            $this->toScreen
        );

        $this->line('PostSelectedEvent Event dispatched.');
    }
}
