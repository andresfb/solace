<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class NewsFeedRunnerModel extends Model
{
    use SoftDeletes;

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.news_feed_runner_connection'));
    }
}
