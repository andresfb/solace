<?php

declare(strict_types=1);

namespace App\Models\Media;

use App\Traits\ConvertDateTimeToTimezone;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use ConvertDateTimeToTimezone;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.default'));
    }
}
