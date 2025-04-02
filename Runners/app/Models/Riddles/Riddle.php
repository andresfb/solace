<?php

namespace App\Models\Riddles;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read string $hash
 * @property-read string $category
 * @property-read string $question
 * @property-read string $answer
 */
class Riddle extends Model
{
    protected $guarded = ['id'];
}
