<?php

return [

    'banded_tags' => explode(',', env('MEDIA_RUNNER_BANDED_TAGS', 'bible,quran')),

    'fulfilled_posts_priority' => env('MEDIA_RUNNER_FULFILED_POSTS_PRIORITY', 80),

    'media_path' => env('MEDIA_RUNNER_STORAGE_PATH'),

];
