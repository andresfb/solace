<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Services;

use App\Events\UpdatePostQueueableEvent;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Common\Dtos\PostUpdateItem;
use Modules\Common\Events\UpdatePostEvent;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Factories\MovieTrailerFactory;
use Modules\EmbyMediaRunner\Traits\CommandExecutable;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class DownloadTrailerService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;
    use CommandExecutable;

    private int $maxRuns = 2;

    private int $runCount = 0;

    public function __construct(
        private readonly YouTubeService $tubeService,
        private readonly VideoService $videoService,
    ) {}

    /**
     * @throws Exception
     */
    public function execute(ProcessMediaItem $mediaItem): PostUpdateItem
    {
        ++$this->runCount;

        foreach ($mediaItem->trailerUrls as $trailerUrl) {
            try {
                $this->line('Checking the URl is a valid URL');

                $trailerUrl = $trailerUrl['Url'];

                if (filter_var($trailerUrl, FILTER_VALIDATE_URL) === false) {
                    throw new RuntimeException('Invalid URL');
                }

                $this->line('Checking the URl is from Youtube');

                if (! $this->tubeService->isYouTube($trailerUrl)) {
                    throw new RuntimeException('YouTube is not a valid URL');
                }

                return $this->processUrl(
                    $mediaItem->movieId,
                    $mediaItem->name,
                    $trailerUrl
                );
            } catch (Exception $e) {
                $this->error($e->getMessage());

                continue;
            }
        }

        // If the downloads failed, and we run this `$this->maxRuns` times
        // we send a request to encode a trailer.
        if ($this->runCount >= $this->maxRuns) {
            $this->error('No trailers downloaded. Encoding one');

            return MovieTrailerFactory::create($mediaItem)
                ->setQueueable($this->queueable)
                ->setToScreen($this->toScreen)
                ->encodeTrailer();
        }

        $this->line('Could not download trailers. Trying again.');

        // We try two times to download the trailers in case
        // there's an internet issue.
        usleep(300000);

        return $this->execute($mediaItem);
    }

    private function processUrl(string $movieId, string $name, string $trailerUrl): PostUpdateItem
    {
        $this->line('Downloading trailer: ' . $trailerUrl);

        $tempPath = md5($movieId.$trailerUrl);
        $processPath = Storage::disk('processing')->path($tempPath);

        $files = $this->download($trailerUrl, $processPath);

        $postUpdateItem = new PostUpdateItem(
            identifier:  $movieId,
            title:  $name,
            mediaFiles: $files,
        );

        $message = "Dispatching %s event for Movie: $name";

        if (! $this->queueable) {
            // if this process is not executed via a job then updating
            // the Post needs to be done in the GenerateMoviePostService
            // process itself, after the Post is created.
            return $postUpdateItem;
        }

        $this->line(sprintf($message, 'UpdatePostQueueableEvent'));

        UpdatePostQueueableEvent::dispatch($postUpdateItem);

        return new PostUpdateItem(
            identifier:  $movieId,
            title:  $name,
        );
    }

    private function download(string $url, string $processPath): Collection
    {
        if (! file_exists($processPath) && ! mkdir($processPath, 0775, true) && ! is_dir($processPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $processPath));
        }

        $cmd = str(config('downloader.yt-dlp-cmd'))
            ->replace('{0}', $processPath)
            ->replace('{1}', $url)
            ->value();

        $this->executeCommand($cmd);

        return $this->checkFiles(
            $this->tubeService->getYtVideoId($url),
            $processPath
        );
    }

    private function checkFiles(?string $videoId, string $processPath): Collection
    {
        if (empty($videoId)) {
            throw new RuntimeException("Video Id is empty");
        }

        $files = $this->videoService->getFiles($videoId, $processPath);
        if ($files->isEmpty()) {
            throw new RuntimeException("Video $videoId not found");
        }

        $files->each(function (string $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'jpg') {
                return;
            }

            if (! $this->videoService->isValid($file)) {
                throw new RuntimeException("Video $file is not valid");
            }
        });

        return $files;
    }
}
