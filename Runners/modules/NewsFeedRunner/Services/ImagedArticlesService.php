<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Services\PostExistsService;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
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
        private readonly ImagedArticleService $articleService,
    ) {}

    /**
     * @param Collection<Article> $articles
     */
    public function execute(Collection $articles): void
    {
        foreach ($articles as $article) {
            if ($this->postExistsService->exists($article->permalink)) {
                continue;
            }

            if ($this->queueable) {
                // TODO implement and dispatch a job to import the article

                continue;
            }

            $this->articleService->setToScreen($this->toScreen)
                ->execute($article);
        }
    }
}
