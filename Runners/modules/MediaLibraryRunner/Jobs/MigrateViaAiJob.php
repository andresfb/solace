<?php

namespace Modules\MediaLibraryRunner\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\MediaLibraryRunner\Services\MigrateViaVisionAiService;

class MigrateViaAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @throws Exception
     */
    public function handle(MigrateViaVisionAiService $service): void
    {
        $service->execute();
    }
}
