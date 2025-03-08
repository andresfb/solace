<?php

namespace App\Models\Posts;

use App\Enums\PostPrivacy;
use App\Enums\PostStatus;
use App\Models\Hashtags\Hashtag;
use App\Models\Hashtags\Scopes\HashtagsScope;
use App\Traits\ConvertDateTimeToTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;
    use ConvertDateTimeToTimezone;
    use SoftDeletes;
    use HasSlug;

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new HashtagsScope);
    }

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'privacy' => PostPrivacy::class,
        ];
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags', 'post_id', 'hashtag_id')
            ->withTimestamps();
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(50);
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
            ->singleFile();
    }
}
