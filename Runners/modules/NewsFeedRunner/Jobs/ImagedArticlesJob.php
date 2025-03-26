<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Services\ImagedArticlesService;

class ImagedArticlesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param Collection<int> $articleIds
     */
    public function __construct(private readonly Collection $articleIds)
    {
    }

    public function handle(ImagedArticlesService $service): void
    {
        $articles = Article::whereIn('id', $this->articleIds->toArray())
            ->get();

        if ($articles->isEmpty()) {
            return;
        }

        $service->execute($articles);
    }
}
