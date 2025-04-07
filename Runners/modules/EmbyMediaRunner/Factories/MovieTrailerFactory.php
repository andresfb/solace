<?php

namespace Modules\EmbyMediaRunner\Factories;

use Modules\Common\Dtos\PostUpdateItem;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Jobs\DownloadTrailerJob;
use Modules\EmbyMediaRunner\Jobs\EncodeTrailerJob;
use Modules\EmbyMediaRunner\Services\DownloadTrailerService;
use Modules\EmbyMediaRunner\Services\EncodeTrailerService;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

final class MovieTrailerFactory
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    private readonly ProcessMediaItem $mediaItem;
    private readonly DownloadTrailerService $downloadTrailerService;
    private readonly EncodeTrailerService $encodeTrailerService;

    private function __construct(ProcessMediaItem $mediaItem)
    {
        $this->mediaItem = $mediaItem;
        $this->downloadTrailerService = app(DownloadTrailerService::class);
        $this->encodeTrailerService = app(EncodeTrailerService::class);
    }

    public static function create(ProcessMediaItem $mediaItem): self
    {
        return new self($mediaItem);
    }

    public function process(): PostUpdateItem
    {
        if ($this->mediaItem->hasTrailerUrls()) {
            return $this->downloadTrailer();
        }

        return $this->encodeTrailer();
    }

    public function downloadTrailer(): PostUpdateItem
    {
        if ($this->queueable) {
            DownloadTrailerJob::dispatch($this->mediaItem)
                ->onConnection($this->getConnection('trailer-download'))
                ->onQueue($this->getQueue('trailer-download'))
                ->delay(now()->addMinutes(5));

            return new PostUpdateItem(
                $this->mediaItem->movieId,
                $this->mediaItem->name
            );
        }

        return $this->downloadTrailerService->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute($this->mediaItem);
    }

    public function encodeTrailer(): PostUpdateItem
    {
        if ($this->queueable) {
            EncodeTrailerJob::dispatch($this->mediaItem)
                ->onConnection($this->getConnection('encode-trailer'))
                ->onQueue($this->getQueue('encode-trailer'))
                ->delay(now()->addMinutes(5));

            return new PostUpdateItem(
                $this->mediaItem->movieId,
                $this->mediaItem->name
            );
        }

        return $this->encodeTrailerService->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute($this->mediaItem);
    }
}
