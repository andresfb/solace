<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Support\Facades\Cache;
use Modules\UserGeneratorRunner\Dtos\UserPicture;

trait ImageCatchable
{
    public function checkImage(string $pictureUrl, int $maxUsages): string
    {
        if ($pictureUrl === '' || $pictureUrl === '0') {
            return '';
        }

        $key = md5($pictureUrl);
        if (! Cache::has($key)) {
            return $this->cacheImage($pictureUrl);
        }

        $cachedPicture = Cache::get($key);
        if ($cachedPicture->usage > $maxUsages) {
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
