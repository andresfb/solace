<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Traits;

use Illuminate\Support\Facades\Cache;
use Modules\UserGeneratorRunner\Dtos\UserPicture;

trait ProfileImageCatchable
{
    public function checkImage(string $pictureUrl): string
    {
        $key = md5($pictureUrl);
        if (! Cache::has($key)) {
            return $this->cacheImage($pictureUrl);
        }

        $cachedPicture = Cache::get($key);
        if ($cachedPicture->usage > config('user_generator.max_new_users')) {
            return '';
        }

        return $this->cacheImage(
            $pictureUrl,
            $cachedPicture->usage + 1
        );
    }

    private function cacheImage(string $pictureUrl, int $usageCount = 1): string
    {
        $userPicture = new UserPicture(
            url: $pictureUrl,
            usage: $usageCount
        );

        Cache::forever(
            key: md5($pictureUrl),
            value: $userPicture
        );

        return $pictureUrl;
    }
}
