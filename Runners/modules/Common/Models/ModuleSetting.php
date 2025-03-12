<?php

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.default'));
    }
}
