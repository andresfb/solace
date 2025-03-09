<?php

return [

    'banded_tags' => explode(',', (string) env('MEDIA_RUNNER_BANDED_TAGS', 'bible,quran')),

    'media_path' => env('MEDIA_RUNNER_STORAGE_PATH'),

    // TODO: this one will be used for the more complex tasks
    'posts_priority' => env('MEDIA_RUNNER_POSTS_PRIORITY', 80),

];
