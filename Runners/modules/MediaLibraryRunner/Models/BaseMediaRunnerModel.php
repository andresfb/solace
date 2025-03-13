<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseMediaRunnerModel extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.media_runner_connection'));
    }
}
