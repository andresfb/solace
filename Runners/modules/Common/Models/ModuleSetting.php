<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $value
 */
class ModuleSetting extends Model
{
    protected $guarded = ['id'];

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.default'));
    }
}
