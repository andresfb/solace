<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\ArticleJob;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class PicsumArticleService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(
        private readonly PicsumPhotosService $photosService,
        private readonly ArticleService $articleService,
    ) {}

    public function execute(Article $article): void
    {
        $image = $this->photosService->setToScreen($this->toScreen)
            ->getImage();

        if (! $image->found) {
            ChangeStatusEvent::dispatch(
                $this->NEWS_FEED,
                $article->id,
                RunnerStatus::UNUSABLE,
            );

            return;
        }

        $this->line("Saving image $image->imageUrl to article");

        Article::where('id', $article->id)
            ->update([
                'thumbnail' => $image->imageUrl,
                'attribution' => $image->getAttribution(),
            ]);

        if ($this->queueable) {
            $this->line('Dispatching ArticleJob');

            ArticleJob::dispatch($article->id, $this->IMPORT_PICSUM_ARTICLE)
                ->onConnection($this->getConnection($this->NEWS_FEED))
                ->onQueue($this->getQueue($this->NEWS_FEED))
                ->delay(now()->addSeconds(5));

            return;
        }

        $this->line('Executing ArticleService...');

        $this->articleService->setToScreen($this->toScreen)
            ->setQueueable(false)
            ->execute(
                Article::find($article->id),
                $this->IMPORT_PICSUM_ARTICLE
            );
    }
}
