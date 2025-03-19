<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\UserGeneratorRunner\Services\GenerateUsersService;

class GenerateUsersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly bool $queueable) {}

    /**
     * @throws Exception
     */
    public function handle(GenerateUsersService $service): void
    {
        $service->setQueueable($this->queueable)
            ->execute();
    }
}
