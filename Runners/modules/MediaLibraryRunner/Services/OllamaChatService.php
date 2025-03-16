<?php

namespace Modules\MediaLibraryRunner\Services;

use Cloudstudio\Ollama\Ollama;
use Modules\Common\Enum\RunnerStatus;
use Modules\MediaLibraryRunner\Models\Media\MediaItem;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class OllamaChatService extends BaseOllamaService
{
    protected function getTaskName(): string
    {
        return $this->POST_CHAT_AI;
    }

    protected function getExtraOllamaOptions(Ollama $ollama): Ollama
    {
        return $ollama;
    }

    protected function getRunnerStatus(): RunnerStatus
    {
        return RunnerStatus::LOST_CAUSE;
    }

    protected function loadMediaInfo(LibraryPost $libraryPost): void
    {
        $this->mediaInfo = MediaItem::loadEmpty();
    }
}
