<?php

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Meilisearch\Client;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Libraries\EmbyApiLibrary;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

final class IndexMediaService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly EmbyApiLibrary $embyApiLibrary) {}

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->line('Indexing Emby Movies');

        $this->indexMovies();
    }

    /**
     * @throws Exception
     */
    public function indexMovies(): void
    {
        $movies = $this->embyApiLibrary->getData(EmbyApiLibrary::MOVIE_TYPE);

        if (empty($movies)) {
            throw new \RuntimeException("No movies found.");
        }

        $client = new Client(
            Config::string('meilisearch.host'),
            Config::string('meilisearch.key'),
        );

        $index = $client->index(
            Config::string('meilisearch.movies_index')
        );

        $index->deleteAllDocuments();
        $index->addDocuments($movies);
    }
}
