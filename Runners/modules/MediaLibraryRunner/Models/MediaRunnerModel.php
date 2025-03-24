<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models;

use Illuminate\Database\Eloquent\Model;

abstract class MediaRunnerModel extends Model
{
    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.media_runner_connection'));
    }
}
