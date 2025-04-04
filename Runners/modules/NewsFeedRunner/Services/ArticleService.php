<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Dtos\PostItem;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ArticleService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function execute(Article $article, string $taskName): void
    {
        $this->line('Loading the Media Files and tags...');

        PostSelectedEvent::dispatch(
            $article->load('feed.provider')->getPostableInfo($taskName),
            $this->toScreen
        );

        $this->line('PostSelectedEvent Event dispatched.');
    }
}
