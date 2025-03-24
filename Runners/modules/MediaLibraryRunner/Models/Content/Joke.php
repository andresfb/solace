<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Content;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $category
 */
class Joke extends ContentModel
{
    protected $table = 'cnt_jokes';
}
