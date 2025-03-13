<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Cloudstudio\Ollama\Facades\Ollama;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Modules\Common\Enum\LibraryPostStatus;
use Modules\Common\Events\PostCreatedEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class OllamaService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function execute(LibraryPost $libraryPost): void
    {
        try {
            $this->line('Asking the AI for Post content');

            $mediaFiles = $libraryPost->getMediaFiles();
            if ($mediaFiles->isEmpty()) {
                PostCreatedEvent::dispatch(
                    $this->MEDIA_LIBRARY,
                    $libraryPost->id,
                    LibraryPostStatus::UNUSABLE
                );
            }

            $mediaInfo = $mediaFiles->first();
            if (!file_exists($mediaInfo->filePath)) {
                PostCreatedEvent::dispatch(
                    $this->MEDIA_LIBRARY,
                    $libraryPost->id,
                    LibraryPostStatus::UNUSABLE
                );
            }

            $imagePath = sprintf(
                config("$this->POST_VIA_AI.ai_readable_file_path"),
                $mediaInfo->filePath,
            );

            $title = Ollama::model(config("$this->POST_VIA_AI.ai_vision_model"))
                ->prompt(config("$this->POST_VIA_AI.ai_post_prompt_title"))
                ->image($imagePath)
                ->ask();

            $content = Ollama::model(config("$this->POST_VIA_AI.ai_vision_model"))
                ->prompt(config("$this->POST_VIA_AI.ai_post_prompt_content"))
                ->image($imagePath)
                ->ask();

            $this->processPost($libraryPost, $title, $content);
        } catch (GuzzleException|Exception $e) {
            Log::error(
                sprintf(
                    "%s %s %s %s",
                    "@OllamaService.execute.",
                    "Error found generating AI content for Library Post Id:",
                    $libraryPost->id,
                    $e->getMessage()
                )
            );
        }
    }

    private function processPost(LibraryPost $libraryPost, Response|array $title, Response|array $content): void
    {
        if ($title instanceof Response) {
            $title = $title->getBody()->getContents();
        } else {
            $title = $title[0];
        }

        if ($content instanceof Response) {
            $content = $content->getBody()->getContents();
        } else {
            $content = $content[0];
        }

        [$hashtags, $content] = $this->extractHashtags($content);

        $postInfo = $libraryPost->getPostableInfo();
        $postInfo['title'] = $title;
        $postInfo['content'] = $content;
        $postInfo['source'] .= strtoupper(':AI_MODEL='.config("$this->POST_VIA_AI.ai_vision_model"));
        $postInfo['hashtags']->push($hashtags);


    }


    private function extractHashtags($string): array
    {
        // Regular expression to match hashtags
        $pattern = '/#\w+/';

        // Extract hashtags
        preg_match_all($pattern, $string, $matches);
        $hashtags = $matches[0];

        // Remove hashtags from the original string
        $stringWithoutHashtags = preg_replace($pattern, '', $string);

        // Trim any extra whitespace
        $stringWithoutHashtags = trim($stringWithoutHashtags);

        return [
            'hashtags' => $hashtags,
            'content' => $stringWithoutHashtags
        ];
    }
}
