<?php

declare(strict_types=1);

namespace App\Models\Profiles;

use App\Enums\ProfileGender;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Profile extends BaseModel implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
            ])->singleFile();

        foreach (config('settings.profile_image_sizes') as $item) {
            $this->addMediaConversion((string) $item)
                ->format('jpg')
                ->width($item)
                ->height($item)
                ->sharpen(10)
                ->performOnCollections('avatar');
        }
    }
}
