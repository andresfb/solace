<?php

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
use Modules\EmbyMediaRunner\Traits\ModuleConstants;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DownloadTrailerService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function __construct(
        private readonly YouTubeService $tubeService,
        private readonly VideoService $videoService,
    ) {}

    public function execute(ProcessMediaItem $mediaItem): PostUpdateItem
    {
        $runCount = 0;
        $urlCount = count($mediaItem->trailerUrls);
        $encoder = MovieTrailerFactory::create($mediaItem)
            ->setQueueable($this->queueable)
            ->setToScreen($this->toScreen);

        foreach ($mediaItem->trailerUrls as $trailerUrl) {
            try {
                $this->line('Checking the URl is a valid URL');

                $trailerUrl = $trailerUrl['Url'];

                if (filter_var($trailerUrl, FILTER_VALIDATE_URL) === false) {
                    throw new \RuntimeException('Invalid URL');
                }

                $this->line('Checking the URl is from Youtube');

                if (! $this->tubeService->isYouTube($trailerUrl)) {
                    throw new \RuntimeException('YouTube is not a valid URL');
                }

                ++$runCount;

                return $this->processUrl(
                    $mediaItem->movieId,
                    $mediaItem->name,
                    $trailerUrl
                );
            } catch (Exception $e) {
                $this->error($e->getMessage());
                Log::error($e->getMessage());

                if ($runCount < $urlCount) {
                    continue;
                }

                return $encoder->encodeTrailer();
            }
        }

        return $encoder->encodeTrailer();
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
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $processPath));
        }

        $cmd = str(config('downloader.yt-dlp-cmd'))
            ->replace('{0}', $processPath)
            ->replace('{1}', $url)
            ->value();

        $this->line("Executing: $cmd");

        $process = Process::fromShellCommandline($cmd)
            ->enableOutput()
            ->setTimeout(0)
            ->mustRun();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = str($process->getOutput());
        if ($result->lower()->contains('error')) {
            throw new \RuntimeException($result->value());
        }

        return $this->checkFiles(
            $this->tubeService->getYtVideoId($url),
            $processPath
        );
    }

    private function checkFiles(?string $videoId, string $processPath): Collection
    {
        if (empty($videoId)) {
            throw new \RuntimeException("Video Id is empty");
        }

        $files = $this->videoService->exists($videoId, $processPath);
        if ($files->isEmpty()) {
            throw new \RuntimeException("Video $videoId not found");
        }

        $files->each(function (string $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'jpg') {
                return;
            }

            if (! $this->videoService->isValid($file)) {
                throw new \RuntimeException("Video $file is not valid");
            }
        });

        return $files;
    }
}
