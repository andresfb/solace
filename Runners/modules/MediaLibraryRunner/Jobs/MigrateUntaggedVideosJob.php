<?php

namespace Modules\MediaLibraryRunner\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\MediaLibraryRunner\Services\MigrateUntaggedVideosService;

class MigrateUntaggedVideosJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @throws Exception
     */
    public function handle(MigrateUntaggedVideosService $service): void
    {
        try {
            $service->execute();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
