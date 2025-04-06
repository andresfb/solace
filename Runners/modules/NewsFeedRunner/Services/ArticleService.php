<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Events\PostSelectedEvent;
use Modules\Common\Events\PostSelectedQueueableEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
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

        $postItem = $article->load('feed.provider')
            ->getPostableInfo($taskName);

        $message = "Dispatching %s event for Article: $postItem->modelId\n";

        if ($this->queueable) {
            $this->line(sprintf($message, 'PostSelectedQueueableEvent'));

            PostSelectedQueueableEvent::dispatch($postItem);

            return;
        }

        $this->line(sprintf($message, 'PostSelectedEvent'));

        PostSelectedEvent::dispatch(
            $postItem,
            $this->toScreen
        );

        $this->line('PostSelectedEvent Event dispatched.');
    }
}
