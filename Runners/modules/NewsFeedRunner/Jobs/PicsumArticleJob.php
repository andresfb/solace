<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Services\PicsumArticleService;

class PicsumArticleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $articleId) {}

    public function handle(PicsumArticleService $service): void
    {
        $article = Article::where('id', $this->articleId)
            ->firstOrFail();

        $service->execute($article);
    }
}
