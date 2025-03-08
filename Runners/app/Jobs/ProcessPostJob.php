<?php

namespace App\Jobs;

use App\Models\Posts\PostItem;
use App\Services\ProcessPostService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPostJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly PostItem $postItem)
    {
    }

    /**
     * @throws Exception|Throwable
     */
    public function handle(ProcessPostService $service): void
    {
        try {
            $service->execute($this->postItem);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
