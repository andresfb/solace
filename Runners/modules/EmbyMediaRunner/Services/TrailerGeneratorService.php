<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Modules\Common\Traits\Screenable;
use Modules\EmbyMediaRunner\Traits\CommandExecutable;
use Modules\EmbyMediaRunner\Traits\VideoDuration;
use RuntimeException;

final class TrailerGeneratorService
{
    use VideoDuration;
    use CommandExecutable;
    use Screenable;

    private string $inputFile = '';

    private string $outputFile = '';

    private string $outputPath = '';

    private float $clipLength; // seconds

    private float $transitionDuration; // seconds

    private float $maxTrailerLength; // seconds (5 min)

    private float $scaleFactor; // 5% of the video

    public function __construct()
    {
        $this->clipLength = Config::float('encode-trailer.clip-length');
        $this->transitionDuration = Config::float('encode-trailer.transition-duration');
        $this->maxTrailerLength = Config::float('encode-trailer.max-trailer-length');
        $this->scaleFactor = Config::float('encode-trailer.scale-factor');
    }

    public function setInputFile(string $inputFile): self
    {
        $this->inputFile = $inputFile;

        return $this;
    }

    public function setOutputFile(string $outputFile): self
    {
        $this->outputFile = $outputFile;
        $this->outputPath = pathinfo($outputFile, PATHINFO_DIRNAME);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function generateTrailer(): void
    {
        if (empty($this->inputFile)) {
            throw new FileNotFoundException('No video file provided');
        }

        if (empty($this->outputFile)) {
            throw new InvalidArgumentException('No output file provided');
        }

        // TODO: Get the video resolution and change the clips command to scale down to 720 if the resolution is bigger
        $duration = $this->getVideoDuration($this->inputFile);
        if ($duration <= 0.0) {
            throw new RuntimeException('Video length not valid');
        }

        $this->line('Full video duration: '.number_format(($duration / 60), 2).' minutes');
        $this->line('Get trailer duration');

        $trimmedDuration = $duration - ($duration * Config::float('encode-trailer.padding_time'));
        $trailerDuration = min($trimmedDuration * $this->scaleFactor, $this->maxTrailerLength);

        $this->line('Trailer duration: '.number_format(($trailerDuration / 60), 2).' minutes');
        $this->line('Calculating clip sections');

        $clipCount = (int) floor($trailerDuration / ($this->clipLength - $this->transitionDuration));
        if ($clipCount < 1) {
            $clipCount = 1;
        }

        $this->line("Calculated $clipCount sections");

        $timestamps = $this->selectTimestamps($trimmedDuration, $clipCount);
        $clipFiles = $this->extractClips($timestamps);

        $this->line('');

        $this->createCrossfadedTrailer($clipFiles);
    }

    private function selectTimestamps(float $videoDuration, int $count): array
    {
        $this->line('Selecting timestamps');

        $timestamps = [];
        $interval = ($videoDuration - $this->clipLength) / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $timestamps[] = round($i * $interval, 2);
        }

        return $timestamps;
    }

    private function extractClips(array $timestamps): array
    {
        $this->line('Extracting clips');

        $clips = [];
        foreach ($timestamps as $i => $start) {
            $clips[] = $this->createClip($i, $start);
        }

        return $clips;
    }

    private function createClip(int $number, float $start): string
    {
        $clipFile = "$this->outputPath/clip_$number.mp4";
        $cmd = sprintf(
            '%s -hide_banner -v error -ss %s -i "%s" -t %s -c:v libx264 -crf 18 -pix_fmt yuv420p -c:a aac -movflags +faststart "%s" -y',
            Config::string('media-library.ffmpeg_path'),
            $start,
            $this->inputFile,
            $this->clipLength,
            $clipFile
        );

        $this->executeCommand($cmd);

        return $clipFile;
    }

    private function createCrossfadedTrailer(array $clips): void
    {
        $index = 0;
        $fadeOffset = 0;
        $previousTempFile = null;
        $current = array_shift($clips);

        $ffmpegSection = sprintf(
            '%s -hide_banner -y -v error -copyts',
            Config::string('media-library.ffmpeg_path')
        );

        $mapSection = '-map "[v]" -map "[a]" -avoid_negative_ts make_zero';
        $encodingSettings = '-c:v libx264 -crf 18 -pix_fmt yuv420p -c:a aac -movflags +faststart';

        foreach ($clips as $clip) {
            $nextTemp = "$this->outputPath/temp_merged_$index.mp4";
            $fadeOffset += $this->clipLength - $this->transitionDuration;

            $filter = sprintf(
                '%s;%s',
                sprintf('[0:v][1:v]xfade=transition=fade:duration=%f:offset=%f[v]', $this->transitionDuration, $fadeOffset),
                sprintf('[0:a][1:a]acrossfade=duration=%f[a]', $this->transitionDuration)
            );

            $cmd = sprintf(
                '%s -i "%s" -i "%s" -filter_complex "%s" %s %s "%s"',
                $ffmpegSection,
                $current,
                $clip,
                $filter,
                $mapSection,
                $encodingSettings,
                $nextTemp
            );

            $this->executeCommand($cmd);

            if ($previousTempFile !== null && file_exists($previousTempFile)) {
                unlink($previousTempFile);
            }

            $current = $nextTemp;
            $previousTempFile = $current;
            $index++;
        }

        rename($current, $this->outputFile);

        $this->line('Finished encoding');
    }
}
