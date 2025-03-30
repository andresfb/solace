<?php

return [

    'posts_limit' => (int) env('IMPORT_PICSUM_ARTICLES_POSTS_LIMIT', 500),

    'max_empty_runs' => (int) env('IMPORT_PICSUM_ARTICLES_MAX_EMPTY_RUN', 10),

    'api_url' => env('PICSUM_URL', 'https://picsum.photos/v2/list?page=%s'),

    'images_max_reuse' => (int) env('PICSUM_IMAGES_MAX_REUSE', 5),

    'max_page_number' => (int) env('PICSUM_IMAGES_MAX_PAGE_NUMBER', 34),

];
