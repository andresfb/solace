<?php

namespace Modules\MediaLibraryRunner\Services;

use Modules\ApiConsumers\Services\Ollama;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Models\MediaItem;
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

    protected function getPrompt(LibraryPost $libraryPost): string
    {
        return sprintf(
            config('post-chat-ai.ai_post_prompt_content'),
            $libraryPost->type,
            $libraryPost->type,
            $this->spark,
        );
    }
}
