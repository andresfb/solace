<?php

namespace App\Models\Hashtags;

use App\Models\BaseModel;
use App\Models\Posts\Post;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hashtag extends BaseModel
{
    use SoftDeletes;

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_hashtags', 'hashtag_id', 'post_id')
            ->withTimestamps();
    }
}
