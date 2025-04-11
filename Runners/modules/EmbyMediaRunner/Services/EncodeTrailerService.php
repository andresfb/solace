<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Common\Dtos\PostUpdateItem;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;
use RuntimeException;

final class EncodeTrailerService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function __construct(
        private readonly TrailerGeneratorService $trailerService,
        private readonly ThumbnailService $thumbnailService,
    ) {}

    /**
     * @throws Exception
     */
    public function execute(ProcessMediaItem $mediaItem): PostUpdateItem
    {
        $this->line('Encoding trailer: ' . $mediaItem->filePath);

        $trailer = $this->prepareOutFile($mediaItem);

        $this->trailerService->setToScreen($this->toScreen)
            ->setInputFile($mediaItem->filePath)
            ->setOutputFile($trailer)
            ->generateTrailer();

        $tempPath = pathinfo($trailer, PATHINFO_DIRNAME);
        $thumbnail = "$tempPath/thumbnail.png";

        $this->thumbnailService->setToScreen($this->toScreen)
            ->setInputFile($mediaItem->filePath)
            ->setOutputFile($thumbnail)
            ->captureThumbnail();

        return new PostUpdateItem(
            identifier: $mediaItem->movieId,
            title: $mediaItem->name,
            mediaFiles: collect($trailer)->add($thumbnail),
        );
    }

    private function prepareOutFile(ProcessMediaItem $mediaItem): string
    {
        if (empty($mediaItem->filePath)) {
            return '';
        }

        $this->line('Preparing out file');

        $tmpPath = md5($mediaItem->filePath);

        $processPath = Storage::disk('processing')->path($tmpPath);

        if (! file_exists($processPath) && ! mkdir($processPath, 0775, true) && ! is_dir($processPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $processPath));
        }

        $videoName = str(pathinfo($mediaItem->filePath, PATHINFO_FILENAME));

        return sprintf(
            "%s/%s.%s",
            $processPath,
            $videoName->slug()->value(),
            'mp4'
        );
    }
}
