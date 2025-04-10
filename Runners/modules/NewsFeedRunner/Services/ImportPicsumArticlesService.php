<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Factories\EmptyRunFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\PicsumArticleJob;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportPicsumArticlesService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly PicsumArticleService $articleService) {}

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $this->line("Loading Articles without Image\n");

        $articles = Article::query()
            ->withoutQuoteBased()
            ->where('thumbnail', '=', '')
            ->where('title', '!=', '')
            ->where('permalink', '!=', '')
            ->where('runner_status', RunnerStatus::STASIS)
            ->where('published_at', '>=', now()->subDays(10))
            ->orderBy('published_at', 'desc')
            ->limit(
                config("$this->IMPORT_PICSUM_ARTICLE.posts_limit")
            )
            ->get();

        if ($articles->isEmpty()) {
            $message = 'No Articles without images found';

            $this->warning($message);

            EmptyRunFactory::handler(
                $this->MODULE_NAME,
                $this->IMPORT_PICSUM_ARTICLE,
                $message
            );
        }

        $articles->map(function (Article $article): void {
            if ($this->queueable) {
                $this->line("Queueing PicsumArticleJob for Article: $article->title");

                PicsumArticleJob::dispatch($article->id)
                    ->onConnection($this->getConnection($this->IMPORT_PICSUM_ARTICLE))
                    ->onQueue($this->getQueue($this->IMPORT_PICSUM_ARTICLE))
                    ->delay(now()->addMinutes(5));

                return;
            }

            $this->line("Processing Article: $article->id - $article->title");

            $this->articleService->setQueueable($this->queueable)
                ->setToScreen($this->toScreen)
                ->execute($article);
        });
    }
}
