<?php

namespace App\Models\Hashtags;

use App\Models\Posts\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hashtag extends Model
{
    use SoftDeletes;

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_hashtags', 'hashtag_id', 'post_id')
            ->withTimestamps();
    }
}
