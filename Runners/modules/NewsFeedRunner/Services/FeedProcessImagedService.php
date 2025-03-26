<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\ImagedArticlesJob;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Models\Feed\Feed;
use Modules\NewsFeedRunner\Models\Provider\Provider;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class FeedProcessImagedService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly ImagedArticlesService $articlesService)
    {
    }

    public function execute(Provider $provider): void
    {
        if ($provider->feeds?->isEmpty()) {
            return;
        }

        foreach ($provider->feeds as $feed) {
            $this->processArticles($feed);
        }
    }

    private function processArticles(Feed $feed): void
    {
        /** @var Collection<Article> $articles */
        Article::query()
            ->where('feed_id', $feed->id)
            ->where('thumbnail', '!=', '')
            ->where('title', '!=', '')
            ->where('permalink', '!=', '')
            ->where('published_at', '>=', now()->subDays($feed->provider->go_back_days))
            ->chunk('50', function (Collection $articles): void {
                if ($this->queueable) {
                    ImagedArticlesJob::dispatch($articles->pluck('id'))
                        ->onQueue(config('news_feed_runner.horizon_queue'))
                        ->delay(now()->addSeconds(5));

                    return;
                }

                $this->articlesService->setQueueable($this->queueable)
                    ->setToScreen($this->toScreen)
                    ->execute($articles);
            });
    }
}
