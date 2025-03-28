<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Modules\Common\Events\PostExistsEvent;

class PostExistsService
{
    public function exists(string $identifier, string $title): bool
    {
        $response = null;

        PostExistsEvent::dispatch(
            $identifier,
            $title,
            static function (bool $result) use (&$response): void {
                $response = $result;
            }
        );

        return $response === true;
    }
}
