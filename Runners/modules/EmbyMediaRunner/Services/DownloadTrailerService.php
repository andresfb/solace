<?php

namespace Modules\EmbyMediaRunner\Services;

use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

class DownloadTrailerService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function execute(ProcessMediaItem $mediaItem): void
    {
        $this->line('Downloading trailer: ' . $mediaItem->trailerUrl);

        // TODO: implement downloading the trailer
    }
}
