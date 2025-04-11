<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\EmbyMediaRunner\Services\IndexMediaService;

class IndexSeriesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $failOnTimeout = true;

    /**
     * @throws Exception
     */
    public function handle(IndexMediaService $service): void
    {
        try {
            $service->indexSeries();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
