<?php

namespace Modules\UserGeneratorRunner\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\UserGeneratorRunner\Traits\ProfileImageCatchable;

class XsGamesService
{
    use ProfileImageCatchable;

    public function getImage(): string
    {
        $url = sprintf(
            config('xsgames.api_url'),
            collect(config('xsgames.options'))->random()
        );

        try {
            $response = Http::get($url);

            if ($response->failed()) {
                return '';
            }

            if (empty($response->handlerStats()) || blank($response->handlerStats()['url'])) {
                return '';
            }

            $image = $this->checkImage(
                $response->handlerStats()['url']
            );

            if ($image === '') {
                return '';
            }

            return $image;
        } catch (ConnectionException) {
            return '';
        }
    }
}
