<?php

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Modules\EmbyMediaRunner\Traits\CommandExecutable;
use Modules\EmbyMediaRunner\Traits\VideoDuration;
use RuntimeException;

class TrailerGeneratorService
{
    use VideoDuration;
    use CommandExecutable;

    private string $inputFile;

    private string $outputFile;

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

        $duration = $this->getVideoDuration($this->inputFile);
        if ($duration <= 0.0) {
            throw new RuntimeException('Video length not valid');
        }

        $trailerDuration = min($duration * $this->scaleFactor, $this->maxTrailerLength);

        $clipCount = floor($trailerDuration / ($this->clipLength - $this->transitionDuration));
        if ($clipCount < 1) {
            $clipCount = 1;
        }

        $timestamps = $this->selectTimestamps($duration, $clipCount);

        $clipFiles = $this->extractClips($timestamps);
        $this->createCrossfadedTrailer($clipFiles);
        $this->cleanup($clipFiles);
    }

    private function selectTimestamps(float $videoDuration, int $count): array
    {
        $timestamps = [];
        $interval = ($videoDuration - $this->clipLength) / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $timestamps[] = round($i * $interval, 2);
        }
        return $timestamps;
    }

    private function extractClips(array $timestamps): array
    {
        $clips = [];

        foreach ($timestamps as $i => $start) {
            $clips[] = $this->createClip($i, $start);
        }

        return $clips;
    }

    private function createCrossfadedTrailer(array $clipFiles): void
    {
        $inputCmd = '';
        $videoLabels = [];
        $audioLabels = [];

        foreach ($clipFiles as $index => $clip) {
            $inputCmd .= "-i \"$clip\" ";
            $videoLabels[] = "[$index:v]";
            $audioLabels[] = "[$index:a]";
        }

        $filterSteps = '';
        $fadeOffset = $this->clipLength - $this->transitionDuration;

        $vIn = $videoLabels[0];
        $aIn = $audioLabels[0];

        for ($i = 1, $iMax = count($clipFiles); $i < $iMax; $i++) {
            $vNext = $videoLabels[$i];
            $aNext = $audioLabels[$i];

            $vOut = "v$i";
            $aOut = "a$i";

            $filterSteps .= sprintf(
                "%s%s xf=xfade=transition=fade:duration=%s:offset=%s [%s];",
                $vIn,
                $vNext,
                $this->transitionDuration,
                $fadeOffset,
                $vOut
            );

            $filterSteps .= "$aIn$aNext af=acrossfade=d=$this->transitionDuration:c1=exp:c2=exp [$aOut];";

            $vIn = "[$vOut]";
            $aIn = "[$aOut]";
        }

        $filterComplex = "-filter_complex \"$filterSteps\" -map $vIn -map $aIn";

        $finalCmd = sprintf(
            '%s %s %s -c:v libx264 -c:a aac "%s" -y',
            Config::string('media-library.ffmpeg_path'),
            $inputCmd,
            $filterComplex,
            $this->outputFile
        );

        $this->executeCommand($finalCmd);
    }

    private function createClip(int $number, float $start): string
    {
        $clipFile = "clip_$number.mp4";
        $cmd = sprintf(
            '%s -ss %s -i "%s" -t %s -c:v libx264 -c:a aac "%s" -y',
            Config::string('media-library.ffmpeg_path'),
            $start,
            $this->inputFile,
            $this->clipLength,
            $clipFile
        );

        $this->executeCommand($cmd);

        return $clipFile;
    }

    private function cleanup(array $files): void
    {
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            unlink($file);
        }
    }
}
