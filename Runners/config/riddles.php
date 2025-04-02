<?php

return [

    'endpoint' => 'https://riddles-api.netlify.app/%s/%s',

    'categories' => [
        'funny',
        'science',
        'math',
        'mystery',
        'logic',
    ],

    'max_items' => env('MAX_RIDDLE_ITEMS', 25),

];
