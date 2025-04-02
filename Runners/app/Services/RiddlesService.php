<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RiddlesService
{
    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $category = (string) collect(Config::array('riddles.categories'))->random();

        $endPoint = sprintf(
            Config::string('riddles.endpoint'),
            $category,
            Config::integer('riddles.max_items'),
        );

        $riddles = $this->callApi($endPoint);
    }

    /**
     * @throws Exception
     */
    private function callApi(string $url): Collection
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->timeout(120)
        ->get($url);

        if ($response->failed()) {
            throw new RuntimeException($response->body());
        }

        $data = $response->json();

        return collect($data);
    }
}
