<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models\Contents;

/**
 * @property int $id
 * @property string $author
 * @property string $category
 */
class Quote extends ContentModel
{
    protected $table = 'cnt_quotes';
}
