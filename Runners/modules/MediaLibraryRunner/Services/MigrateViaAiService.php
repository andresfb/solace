<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Jobs\OllamaVisionJob;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateViaAiService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly OllamaVisionService $ollamaService) {}

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $libraryPosts = LibraryPost::query()
            ->imagePosts()
            ->oldest()
            ->limit(
                config("$this->POST_VIA_AI.posts_limit")
            )
            ->get();

        if ($libraryPosts->isEmpty()) {
            $message = 'No Library Post for AI found';

            $this->warning($message);

            throw new EmptyRunException(
                $this->MODULE_NAME,
                $this->POST_VIA_AI,
                $message
            );
        }

        $libraryPosts->each(function (LibraryPost $libraryPost): void {
            if ($this->queueable) {
                $this->line('Queueing OllamaVisionJob for LibraryPost: '.$libraryPost->id);

                OllamaVisionJob::dispatch($libraryPost)
                    ->onConnection($this->getConnection($this->POST_VIA_AI))
                    ->onQueue($this->getQueue($this->POST_VIA_AI))
                    ->delay(now()->addMinute());

                return;
            }

            $this->line('Sending Library post to the AI...');

            $this->ollamaService->setToScreen($this->toScreen)
                ->execute($libraryPost);
        });
    }
}
