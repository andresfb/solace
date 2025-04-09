<?php

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Modules\Common\Traits\Screenable;
use Modules\EmbyMediaRunner\Traits\CommandExecutable;
use Modules\EmbyMediaRunner\Traits\VideoDuration;
use RuntimeException;

final class ThumbnailService
{
    use VideoDuration;
    use CommandExecutable;
    use Screenable;

    private string $inputFile;

    private string $outputFile;

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
    public function captureThumbnail(): void
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

        $min = 0.62;
        $max = 0.78;
        $timeCode = $min + mt_rand() / mt_getrandmax() * ($max - $min);

        $thumbnailTime = gmdate("H:i:s", floor($duration * $timeCode));

        $thumbnailCmd = sprintf(
            '%s -hide_banner -y -v error -ss %s -i "%s" -frames:v 1 -q:v 2 "%s"',
            Config::string('media-library.ffmpeg_path'),
            $thumbnailTime,
            $this->inputFile,
            $this->outputFile
        );

        $this->executeCommand($thumbnailCmd);
    }
}
