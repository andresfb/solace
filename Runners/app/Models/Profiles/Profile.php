<?php

namespace App\Models\Profiles;

use App\Enums\ProfileGender;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use SoftDeletes;

    // TODO: add media library for Profile and Cover pictures
    // TODO: add the Model Settings package to store the social_links

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'gender' => ProfileGender::class,
            'private' => 'boolean',
            'verified' => 'boolean',
            'humanoid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
