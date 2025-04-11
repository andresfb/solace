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
        $this->line('');

        $this->line('Indexing Emby Movies'.PHP_EOL);
        $this->indexMovies();

        $this->line('Indexing Emby TV Series'.PHP_EOL);
        $this->indexSeries();
    }

    /**
     * @throws Exception
     */
    public function indexMovies(): void
    {
        $movies = $this->embyApiLibrary->getMovies();

        if (empty($movies)) {
            throw new \RuntimeException("No movies found.");
        }

        $this->savetoIndex(
            $movies,
            config('meilisearch.movies_index')
        );
    }

    /**
     * @throws Exception
     */
    public function indexSeries(): void
    {
        $series = $this->embyApiLibrary->getSeries();

        if (empty($series)) {
            throw new \RuntimeException("No series found.");
        }

        $this->getSeriesInfo($series);

        $this->savetoIndex(
            $series,
            config('meilisearch.series_index')
        );
    }

    private function getSeriesInfo(array $series): void
    {
        foreach ($series as $item) {
            $this->character('.');

            try {
                $item->Seasons = $this->embyApiLibrary->getSeriesSeasons($item->Id);

                $item->Episodes = $this->embyApiLibrary->getSeriesEpisodes($item->Id);
            } catch (Exception $e) {
                $this->error($e->getMessage().PHP_EOL);
            }
        }
    }

    private function savetoIndex(array $data, string $indexName): void
    {
        $client = new Client(
            Config::string('meilisearch.host'),
            Config::string('meilisearch.key'),
        );

        $index = $client->index($indexName);

        $index->deleteAllDocuments();
        $index->addDocuments($data);
    }
}
