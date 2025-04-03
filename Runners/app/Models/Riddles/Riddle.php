<?php

declare(strict_types=1);

namespace App\Models\Riddles;

use App\Models\Cyphers\Cypher;
use App\Models\Riddles\Scopes\CypherScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read string $hash
 * @property-read string $category
 * @property-read string $question
 * @property-read string $encoded
 * @property-read string $answer
 */
class Riddle extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new CypherScope);
    }

    public function encoded(): Attribute
    {
        return new Attribute(
            get: fn ($value) => base64_decode($value),
            set: fn ($value) => base64_encode($value),
        );
    }

    public function cypher(): BelongsTo
    {
        return $this->belongsTo(Cypher::class);
    }
}
