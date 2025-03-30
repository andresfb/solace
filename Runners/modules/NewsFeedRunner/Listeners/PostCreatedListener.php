<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class PostCreatedListener implements ShouldQueue
{
    use ModuleConstants;

    public function handle(ChangeStatusEvent $event): void
    {
        Article::where('id', $event->modelId)
            ->update([
                'read_at' => now(),
                'runner_status' => $event->runnerStatus,
            ]);
    }

    public function shouldQueue(ChangeStatusEvent $event): bool
    {
        return $event->origin === $this->NEWS_FEED;
    }

    public function viaQueue(): string
    {
        return config("$this->MODULE_NAME.horizon_queue");
    }

    public function withDelay(): int
    {
        return 2; // Seconds
    }
}
