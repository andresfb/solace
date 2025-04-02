<?php

namespace App\Jobs;

use App\Services\RiddlesService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RiddlesCollectorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @throws Exception
     */
    public function handle(RiddlesService $service): void
    {
        try {
            $service->execute();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
