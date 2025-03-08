<?php

namespace App\Models\Media;

use App\Traits\ConvertDateTimeToTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use ConvertDateTimeToTimezone;
    use SoftDeletes;
}
