<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\ApiConsumers\Services\OpenAiClient;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\ArticleJob;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class AiArticleService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;
    use QueueSelectable;

    public function __construct(
        private readonly OpenAiClient $aiClient,
        private readonly ArticleService $articleService
    ) {}

    public function execute(Article $article): void
    {
        $response = $this->aiClient->setModel(config('ai-article-importer.model'))
            ->setUserPrompt($article->title)
            ->image();

        if (! $response->generated) {
            $this->error('We did not get a response from the AI');

            ChangeStatusEvent::dispatch(
                $this->NEWS_FEED,
                $article->id,
                RunnerStatus::UNUSABLE,
            );

            return;
        }

        Article::where('id', $article->id)
            ->update([
                'thumbnail' =>$response->image
            ]);

        if ($this->queueable) {
            ArticleJob::dispatch($article->id, $this->IMPORT_AI_ARTICLE)
                ->onConnection($this->getConnection($this->NEWS_FEED))
                ->onQueue($this->getQueue($this->NEWS_FEED))
                ->delay(now()->addSeconds(5));

            return;
        }

        $this->articleService->setToScreen($this->toScreen)
            ->execute(
                Article::find($article->id),
                $this->IMPORT_AI_ARTICLE
            );
    }
}
