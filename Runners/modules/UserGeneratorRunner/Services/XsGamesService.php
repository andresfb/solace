<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\Common\Traits\ImageCatchable;

class XsGamesService
{
    use ImageCatchable;

    public function getImage(): string
    {
        $url = sprintf(
            config('xsgames.api_url'),
            collect((array) config('xsgames.options'))->random()
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
                $response->handlerStats()['url'],
                config('user_generator.max_new_users')
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
