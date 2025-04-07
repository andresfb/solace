<?php

declare(strict_types=1);

namespace App\Models\Posts;

use App\Enums\PostPrivacy;
use App\Enums\PostStatus;
use App\Models\BaseModel;
use App\Models\Hashtags\Hashtag;
use App\Models\Hashtags\Scopes\HashtagsScope;
use App\Traits\ConvertDateTimeToTimezone;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends BaseModel implements HasMedia
{
    use ConvertDateTimeToTimezone;
    use InteractsWithMedia;
    use Sluggable;
    use SoftDeletes;

    protected $guarded = [
        'id',
        'slug',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new HashtagsScope);
        static::creating(static function (Post $post): void {
            $post->slug = $post->getSlug();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'privacy' => PostPrivacy::class,
            'responses' => 'json',
        ];
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags', 'post_id', 'hashtag_id')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')
            ->singleFile();

        $this->addMediaCollection('thumb')
            ->acceptsMimeTypes([
                'image/jpeg',
            ])->singleFile();

        $this->addMediaCollection('image')
            ->withResponsiveImages();

        $this->addMediaCollection('trailer')
            ->acceptsMimeTypes([
                'video/mp4',
            ]);

        $this->addMediaCollection('trailer-image')
            ->acceptsMimeTypes([
                'image/jpeg',
            ]);
    }
}
