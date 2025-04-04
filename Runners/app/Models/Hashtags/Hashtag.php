<?php

declare(strict_types=1);

namespace App\Models\Hashtags;

use App\Models\BaseModel;
use App\Models\Posts\Post;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hashtag extends BaseModel
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(static function (Hashtag $tag): void {
            $tag->slug = str($tag->name)->slug()->value();
        });
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_hashtags', 'hashtag_id', 'post_id')
            ->withTimestamps();
    }
}
