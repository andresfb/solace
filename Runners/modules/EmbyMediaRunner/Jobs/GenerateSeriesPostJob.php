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
use Modules\EmbyMediaRunner\Services\GenerateSeriesPostService;

class GenerateSeriesPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $failOnTimeout = true;

    /**
     * @throws Exception
     */
    public function handle(GenerateSeriesPostService $service): void
    {
        try {
            $service->execute();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
