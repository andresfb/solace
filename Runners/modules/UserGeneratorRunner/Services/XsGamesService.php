<?php

namespace Modules\UserGeneratorRunner\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\UserGeneratorRunner\Traits\ProfileImageCatchable;
use Multiavatar;

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
                return $this->getFallbackImage();
            }

            if (empty($response->handlerStats()) || blank($response->handlerStats()['url'])) {
                return $this->getFallbackImage();
            }

            $image = $this->checkImage(
                $response->handlerStats()['url']
            );

            if ($image === '') {
                return $this->getFallbackImage();
            }

            return $image;
        } catch (ConnectionException) {
            return $this->getFallbackImage();
        }
    }

    public function getFallbackImage(): string
    {
        $multiAvatar = new Multiavatar();

        return $multiAvatar(Str::random(32), null, null);
    }
}
