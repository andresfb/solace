<?php

namespace Modules\MediaLibraryRunner\Services;

use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;

class MigrateUntaggedVideosService implements TaskServiceInterface
{
    use Screenable;
    use SendToQueue;

    public function execute(): void
    {

    }
}
