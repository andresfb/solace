<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\AiArticlesJob;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Models\Feed\Feed;
use Modules\NewsFeedRunner\Models\Provider\Provider;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class FeedProcessAiService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;
    use QueueSelectable;

    public function __construct(private readonly AiArticleService $articleService) {}

    public function execute(Provider $provider): void
    {
        if ($provider->feeds->isEmpty()) {
            return;
        }

        $this->line("Processing Feeds...\n");

        /** @var Feed $feed */
        foreach ($provider->feeds as $feed) {
            $this->processArticles($feed);
        }
    }

    private function processArticles(Feed $feed): void
    {
        $this->line("Loading Articles for Feed: $feed->title\n");

        $feed->load('provider');

        $goBackDay = $feed->provider->go_back_days * 2;

        Article::query()
            ->where('feed_id', $feed->id)
            ->where('thumbnail', '=', '')
            ->where('title', '!=', '')
            ->where('permalink', '!=', '')
            ->whereNull('read_at')
            ->where('published_at', '>=', now()->subDays($goBackDay))
            ->limit(
                config("$this->IMPORT_AI_ARTICLE.posts_limit")
            )
            ->get()
            ->map(function (Article $article): void {
                if ($this->queueable) {
                    $this->line("Queueing AiArticlesJob for Article: $article->title");

                    AiArticlesJob::dispatch($article->id)
                        ->onConnection($this->getConnection($this->IMPORT_AI_ARTICLE))
                        ->onQueue($this->getQueue($this->IMPORT_AI_ARTICLE))
                        ->delay(now()->addMinutes(5));

                    return;
                }

                $this->line("Processing Articles: $article->title");

                $this->articleService->setQueueable($this->queueable)
                    ->setToScreen($this->toScreen)
                    ->execute($article);
            });
    }
}
