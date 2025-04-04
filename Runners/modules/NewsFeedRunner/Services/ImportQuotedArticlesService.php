<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Factories\EmptyRunFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\QuotedArticleJob;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportQuotedArticlesService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly QuotedArticleService $quotedArticleService) {}

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $this->line("Loading Quote-based Articles\n");

        $articles = Article::select('articles.*')
            ->join('feeds', fn(JoinClause $join) => $join->on('feeds.id', '=', 'articles.feed_id')
                ->whereIn('feeds.id', Config::array('news_feed_runner.quote-based-feeds')))
            ->join('providers', fn(JoinClause $join) => $join->on('providers.id', '=', 'feeds.provider_id')
                ->whereIn('providers.id', Config::array('news_feed_runner.quote-based-providers')))
            ->where('articles.title', '!=', '')
            ->where('articles.permalink', '!=', '')
            ->where('articles.runner_status', RunnerStatus::STASIS)
            ->where('articles.published_at', '>=', DB::raw("DATE_ADD(NOW(), INTERVAL -providers.go_back_days DAY)"))
            ->orderBy('articles.published_at', 'desc')
            ->limit(
                config("$this->IMPORT_QUOTED_ARTICLE.posts_limit")
            )
            ->get();

        if ($articles->isEmpty()) {
            $message = 'No Quote-based Articles found';

            $this->warning($message);

            EmptyRunFactory::handler(
                $this->MODULE_NAME,
                $this->IMPORT_QUOTED_ARTICLE,
                $message
            );
        }

        $articles->map(function (Article $article): void {
            if ($this->queueable) {
                $this->line("Queueing QuotedArticleJob for Article: $article->title");

                QuotedArticleJob::dispatch($article->id)
                    ->onConnection($this->getConnection($this->IMPORT_QUOTED_ARTICLE))
                    ->onQueue($this->getQueue($this->IMPORT_QUOTED_ARTICLE))
                    ->delay(now()->addMinutes(5));

                return;
            }

            $this->line("Processing Article: $article->id - $article->title");

            $this->quotedArticleService->setQueueable($this->queueable)
                ->setToScreen($this->toScreen)
                ->execute($article);

            dd('just one');
        });
    }
}
