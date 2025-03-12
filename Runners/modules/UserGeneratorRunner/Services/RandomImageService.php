<?php

namespace Modules\UserGeneratorRunner\Services;

use Modules\UserGeneratorRunner\Traits\ProfileImageCatchable;

readonly class RandomImageService
{
    use ProfileImageCatchable;

    public function __construct(private XsGamesService $altImageService) {}

    public function getImage(string $pictureUrl): string
    {
        $image = $this->checkImage($pictureUrl);
        if ($image === '') {
            return $this->altImageService->getImage();
        }

        return $image;
    }
}
