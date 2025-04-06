<?php

return [

    'host' => env('MEILISEARCH_HOST'),

    'key' => env('MEILISEARCH_KEY'),

    'movies_index' => env('MEILISEARCH_MOVIES_INDEX', 'emby_movies_index'),

];
