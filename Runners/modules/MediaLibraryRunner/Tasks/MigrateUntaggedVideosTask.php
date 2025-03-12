<?php

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Tasks\BaseTask;
use Modules\MediaLibraryRunner\Jobs\MigrateUntaggedVideosJob;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateUntaggedVideosTask extends BaseTask
{
    use ModuleConstants;

    protected function getModuleName(): string
    {
        return $this->MODULE_NAME;
    }

    protected function getTaskName(): string
    {
        return $this->UNTAGGED_VIDEOS;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to MigrateUntaggedVideosJob');

        MigrateUntaggedVideosJob::dispatch($this->queueable)
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
