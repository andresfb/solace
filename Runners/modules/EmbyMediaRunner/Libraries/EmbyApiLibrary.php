<?php

namespace Modules\EmbyMediaRunner\Libraries;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\EmbyMediaRunner\Dtos\EmbyMediaInfo;

class EmbyApiLibrary
{
    protected array $endPoints = [];

    public function __construct()
    {
        $baseUrl = config('emby_media_runner.api.url');

        $this->endPoints = [
            'movies' => sprintf(
                $baseUrl,
                'Users/'.Config::string('emby_media_runner.user_id').'/Items',
                http_build_query(
                    Config::array('emby_media_runner.api.movie_url_strings')
                ),
            ),
            'series' => sprintf(
                $baseUrl,
                'Users/'.Config::string('emby_media_runner.user_id').'/Items',
                http_build_query(
                    Config::array('emby_media_runner.api.series_url_strings')
                ),
            ),
        ];
    }

    public function getMovies(): ?EmbyMediaInfo
    {
        return $this->getData('movies');
    }

    public function getSeries(): ?EmbyMediaInfo
    {
        return $this->getData('series');
    }

    private function getData(string $type): ?EmbyMediaInfo
    {
        try {
            if (! array_key_exists($type, $this->endPoints)) {
                throw new \RuntimeException('Invalid type');
            }

            $response = $this->getResponse($this->endPoints[$type]);

            $movies = json_decode($response);

            // TODO: use Meilisearch to store this information
            // https://github.com/meilisearch/meilisearch-php?tab=readme-ov-file#-learn-more
            // https://chatgpt.com/share/67f0a328-29a4-800e-87ae-126ddaf621eb

            return EmbyMediaInfo::create(
                $type,
                $this->getResponse($this->endPoints[$type])
            );
        } catch (Exception $e) {
            Log::error('@EmbyApiLibrary.getData: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function getResponse(string $url): string
    {
        return Http::accept('application/json')
            ->get($url)
            ->throw()
            ->body();
    }
}
