<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\NewsFeedRunner\Models\Article\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class PostCreatedListener implements ShouldQueue
{
    use ModuleConstants;

    public function handle(ChangeStatusEvent $event): void
    {
        Article::where('id', $event->modelId)
            ->update(['read_at' => now()]);
    }

    public function shouldQueue(ChangeStatusEvent $event): bool
    {
        return $event->origin === $this->NEWS_FEED
            && ($event->runnerStatus === RunnerStatus::PUBLISHED
                || $event->runnerStatus === RunnerStatus::UNUSABLE);
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
