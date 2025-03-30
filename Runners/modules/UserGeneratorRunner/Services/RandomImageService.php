<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Services;

use Modules\Common\Traits\ImageCatchable;

readonly class RandomImageService
{
    use ImageCatchable;

    public function __construct(private XsGamesService $altImageService) {}

    public function getImage(string $pictureUrl): string
    {
        $image = $this->checkImage(
            $pictureUrl,
            config('user_generator.max_new_users')
        );

        if ($image === '') {
            return $this->altImageService->getImage();
        }

        return $image;
    }
}
