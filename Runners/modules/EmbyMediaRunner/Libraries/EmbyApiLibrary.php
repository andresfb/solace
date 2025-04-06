<?php

namespace Modules\EmbyMediaRunner\Libraries;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbyApiLibrary
{
    public const string MOVIE_TYPE = 'movies';

    public const string SERIES_TYPE = 'series';

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
        ];
    }

    /**
     * @throws Exception
     */
    public function getData(string $type): array
    {
        try {
            if (! array_key_exists($type, $this->endPoints)) {
                throw new \RuntimeException('Invalid type');
            }

            $response = json_decode(
                $this->getResponse($this->endPoints[$type]), false, 512, JSON_THROW_ON_ERROR
            );

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
