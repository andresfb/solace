<?php

return [

    'url' => env('EMBY_API_URL'),

    'movie_url_strings' => [
        'Recursive' => 'true',
        'IncludeItemTypes' => 'Movie',
        'ExcludeItemTypes' => 'Episode',
        'EnableImages' => 'true',
        'EnableUserData' => 'false',
        'IsLocked' => 'false',
        'IsPlayed' => 'false',
        'ParentId' => env('EMBY_MOVIES_PARENT_ID'),
        'Fields' => 'Overview,ProductionYear,Genres,TagLines,OfficialRating,People,CriticRating,RunTimeTicks,Path,Tags,RemoteTrailers',
    ],

    'series_url_strings' => [
        'Recursive' => 'true',
        'IncludeItemTypes' => 'Series',
        'IsPlayed' => 'false',
        'IsLocked' => 'false',
        'EnableImages' => 'true',
        'EnableUserData' => 'false',
        'Fields' => 'Overview,ProductionYear,Genres,DateCreated,TagLines,OfficialRating',
    ],

    'movies_list_page' => env('EMBY_SERVER_MOVIE_LIST_PAGE'),

    'series_list_page' => env('EMBY_SERVER_SERIES_LIST_PAGE'),

    'item_url' => env('EMBY_SERVER_ITEM_PAGE'),

    'image_url' => env('EMBY_API_IMAGE_URL'),

    'user_id' => env('EMBY_USER_ID'),

];
