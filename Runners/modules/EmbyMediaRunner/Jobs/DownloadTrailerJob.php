<?php

namespace Modules\EmbyMediaRunner\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Services\DownloadTrailerService;

class DownloadTrailerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly ProcessMediaItem $mediaItem) {}

    /**
     * @throws Exception
     */
    public function handle(DownloadTrailerService $service): void
    {
        try {
            $service->execute($this->mediaItem);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
