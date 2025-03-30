<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Services\PostExistsService;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\ArticleJob;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImagedArticlesService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(
        private readonly PostExistsService $postExistsService,
        private readonly ArticleService    $articleService,
    ) {}

    /**
     * @param  Collection<Article>  $articles
     */
    public function execute(Collection $articles): void
    {
        foreach ($articles as $article) {
            $this->line('Checking if the article exists...');

            if ($this->postExistsService->exists($article->permalink, $article->title)) {
                $this->line("Article already exists, skipping...\n");

                continue;
            }

            $this->line('Article does not exist, processing...');

            if ($this->queueable) {
                $this->line('Queuing ImagedArticleJob job...');

                ArticleJob::dispatch($article->id, $this->IMPORT_IMAGED_ARTICLES)
                    ->onConnection($this->getConnection($this->NEWS_FEED))
                    ->onQueue($this->getQueue($this->NEWS_FEED))
                    ->delay(now()->addSeconds(5));

                continue;
            }

            $this->line('Importing the article...');

            $this->articleService->setToScreen($this->toScreen)
                ->execute(
                    $article,
                    $this->IMPORT_IMAGED_ARTICLES
                );
        }
    }
}
