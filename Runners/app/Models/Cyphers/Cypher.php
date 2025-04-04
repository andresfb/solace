<?php

declare(strict_types=1);

namespace App\Models\Cyphers;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $clue
 * @property-read string $class
 * @property-read bool $active
 */
class Cypher extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
