<?php

namespace Modules\MediaLibraryRunner\Services;

use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;

class MigrateUntaggedVideosService
{
    use Screenable;
    use SendToQueue;

    public function execute(): void
    {

    }
}
