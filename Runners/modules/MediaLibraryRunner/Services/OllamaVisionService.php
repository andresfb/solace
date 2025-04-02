<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Exception;
use Modules\ApiConsumers\Services\Ollama;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;

class OllamaVisionService extends BaseOllamaService
{
    protected function getTaskName(): string
    {
        return $this->POST_VISION_AI;
    }

    protected function getPrompt(LibraryPost $libraryPost): string
    {
        return sprintf(
            config('post-vision-ai.ai_post_prompt_content'),
            $this->spark
        );
    }

    /**
     * @throws Exception
     */
    protected function getExtraOllamaOptions(Ollama $ollama): Ollama
    {
        return $ollama->image($this->mediaInfo->filePath);
    }

    protected function getRunnerStatus(): RunnerStatus
    {
        return RunnerStatus::REPROCESS;
    }

    protected function loadMediaInfo(LibraryPost $libraryPost): void
    {
        $this->line('Loading Media Files');

        $mediaFiles = $libraryPost->getMediaFiles();
        if ($mediaFiles->isEmpty()) {
            ChangeStatusEvent::dispatch(
                $this->MEDIA_LIBRARY,
                $libraryPost->id,
                RunnerStatus::UNUSABLE
            );
        }

        $this->mediaInfo = $mediaFiles->first();
        if (! file_exists($this->mediaInfo->filePath)) {
            ChangeStatusEvent::dispatch(
                $this->MEDIA_LIBRARY,
                $libraryPost->id,
                RunnerStatus::UNUSABLE
            );
        }
    }
}
