<?php

namespace Modules\EmbyMediaRunner\Libraries;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class EmbyApiLibrary
{
    public const string MOVIE_TYPE = 'movies';

    public const string SERIES_TYPE = 'series';

    public const string SEASONS_TYPE = 'seasons';

    public const string EPISODES_TYPE = 'episodes';

    protected array $endPoints = [];

    public function __construct()
    {
        $baseUrl = config('emby-api.url');

        $this->endPoints = [
            self::MOVIE_TYPE => sprintf(
                $baseUrl,
                'Users/'.Config::string('emby-api.user_id').'/Items',
                http_build_query(
                    Config::array('emby-api.movie_url_strings')
                ),
            ),
            self::SERIES_TYPE => sprintf(
                $baseUrl,
                'Users/'.Config::string('emby-api.user_id').'/Items',
                http_build_query(
                    Config::array('emby-api.series_url_strings')
                ),
            ),
            self::SEASONS_TYPE => sprintf(
                $baseUrl,
                'Shows/{0}/Seasons',
                http_build_query(
                    Config::array('emby-api.series_url_strings')
                ),
            ),
            self::EPISODES_TYPE => sprintf(
                $baseUrl,
                'Shows/{0}/Episodes',
                http_build_query(
                    Config::array('emby-api.episodes_url_strings')
                ),
            ),
        ];
    }

    /**
     * @throws Exception
     */
    public function getMovies(): array
    {
        $url = $this->endPoints[self::MOVIE_TYPE];

        return $this->getData($url);
    }
    /**
     * @throws Exception
     */
    public function getSeries(): array
    {
        $url = $this->endPoints[self::SERIES_TYPE];

        return $this->getData($url);
    }

    /**
     * @throws Exception
     */
    public function getSeriesSeasons(string $seriesId): array
    {
        $endpoint = $this->endPoints[self::SEASONS_TYPE];
        $url = str($endpoint)->replace('{0}', $seriesId)->value();

        return $this->getData($url);
    }

    /**
     * @throws Exception
     */
    public function getSeriesEpisodes(string $seriesId): array
    {
        $endpoint = $this->endPoints[self::EPISODES_TYPE];
        $url = str($endpoint)->replace('{0}', $seriesId)->value();

        return $this->getData($url);
    }

    /**
     * @throws Exception
     */
    private function getData(string $url): array
    {
        try {
            $response = json_decode($this->getResponse($url), false, 512, JSON_THROW_ON_ERROR);

            if (! $response || ! $response->Items) {
                throw new \RuntimeException('Invalid response');
            }

            return $response->Items;
        } catch (Exception $e) {
            Log::error('@EmbyApiLibrary.getData: '.$e->getMessage());

            throw $e;
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
