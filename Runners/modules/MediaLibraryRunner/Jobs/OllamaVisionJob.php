<?php

namespace Modules\MediaLibraryRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;
use Modules\MediaLibraryRunner\Services\OllamaVisionService;

class OllamaVisionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly LibraryPost $libraryPost) {}

    public function handle(OllamaVisionService $service): void
    {
        $service->execute($this->libraryPost);
    }
}
