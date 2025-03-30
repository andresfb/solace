<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Services\ArticleService;

class ArticleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $articleId,
        private readonly string $taskName,
    ) {}

    public function handle(ArticleService $service): void
    {
        $article = Article::where('id', $this->articleId)
            ->firstOrFail();

        $service->execute($article, $this->taskName);
    }
}
