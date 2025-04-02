<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\NewsFeedRunner\Models\Providers\Provider;
use Modules\NewsFeedRunner\Services\FeedProcessImagedService;

class FeedProcessImagedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Provider $provider) {}

    public function handle(FeedProcessImagedService $service): void
    {
        $service->execute($this->provider);
    }
}
