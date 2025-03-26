<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Factories\EmptyRunFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\FeedProcessJob;
use Modules\NewsFeedRunner\Models\Provider\Provider;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportImagedArticlesServices implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly FeedProcessImagedService $feedService)
    {
    }

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $providers = Provider::query()
            ->activeWithFeeds()
            ->get();

        if ($providers->isEmpty()) {
            $message = 'No Active News Providers found';

            $this->warning($message);

            EmptyRunFactory::handler(
                $this->MODULE_NAME,
                $this->IMPORT_IMAGED_ARTICLES,
                $message
            );
        }

        $providers->each(function (Provider $provider): void {
            if ($this->queueable) {
                $this->line('Queueing FeedProcessJob for Provider: '.$provider->id);

                FeedProcessJob::dispatch($provider)
                    ->onQueue(config('news_feed_runner.horizon_queue'))
                    ->delay(now()->addMinutes(5));

                return;
            }

            $this->line('Sending feed for processing...');

            $this->feedService->setToScreen($this->toScreen)
                ->setQueueable($this->queueable)
                ->execute($provider);
        });
    }
}
