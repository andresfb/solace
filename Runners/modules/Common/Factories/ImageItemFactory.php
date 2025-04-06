<?php

namespace Modules\Common\Factories;

use Modules\Common\Dtos\RemoteImageItem;
use Modules\Common\Dtos\StorageImageItem;

final readonly class ImageItemFactory
{
    public static function getItem(string $image): RemoteImageItem|StorageImageItem
    {
        if (str($image)->startsWith('http')) {
            return new RemoteImageItem($image);
        }

        return new StorageImageItem($image);
    }
}
