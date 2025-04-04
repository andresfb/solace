<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Factories\EmptyRunFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Jobs\OllamaChatJob;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateViaChatAiService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly OllamaChatService $ollamaService) {}

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $libraryPosts = LibraryPost::query()
            ->bandedReprocess()
            ->oldest()
            ->limit(
                config("$this->POST_CHAT_AI.posts_limit")
            )
            ->get();

        if ($libraryPosts->isEmpty()) {
            $message = 'No Library Post for AI found';

            $this->warning($message);

            EmptyRunFactory::handler(
                $this->MODULE_NAME,
                $this->POST_CHAT_AI,
                $message
            );
        }

        $libraryPosts->each(function (LibraryPost $libraryPost): void {
            if ($this->queueable) {
                $this->line('Queueing OllamaChatJob for LibraryPost: '.$libraryPost->id);

                OllamaChatJob::dispatch($libraryPost)
                    ->onConnection($this->getConnection($this->POST_CHAT_AI))
                    ->onQueue($this->getQueue($this->POST_CHAT_AI))
                    ->delay(now()->addMinutes(5));

                return;
            }

            $this->line('Sending Library post to the AI...');

            $this->ollamaService->setToScreen($this->toScreen)
                ->setQueueable($this->queueable)
                ->execute($libraryPost);
        });
    }
}
