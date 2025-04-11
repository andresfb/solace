<?php

return [

    'url' => env('EMBY_API_URL'),

    'movie_url_strings' => [
        'Recursive' => 'true',
        'IncludeItemTypes' => 'Movie',
        'ExcludeItemTypes' => 'Episode',
        'EnableImages' => 'true',
        'EnableUserData' => 'true',
        'IsLocked' => 'false',
        'ParentId' => env('EMBY_MOVIES_PARENT_ID'),
        'Fields' => 'Overview,ProductionYear,Genres,TagLines,OfficialRating,People,CriticRating,RunTimeTicks,Path,Tags,RemoteTrailers',
    ],

    'series_url_strings' => [
        'Recursive' => 'true',
        'IncludeItemTypes' => 'Series',
        'IsLocked' => 'false',
        'EnableImages' => 'true',
        'EnableUserData' => 'true',
        'ParentId' => env('EMBY_SERIES_PARENT_ID'),
        'Fields' => 'Overview,ProductionYear,Genres,TagLines,OfficialRating,People,CriticRating,RunTimeTicks,Path,Tags,RemoteTrailers,EndDate',
    ],

    'seasons_url_strings' => [
        'EnableImages' => 'false',
        'EnableImageTypes' => 'false',
        'EnableUserData' => 'false',
        'Fields' => 'Overview,Taglines',
    ],

    'episodes_url_strings' => [
        'EnableImages' => 'false',
        'Fields' => 'Overview,Path',
    ],

    'movies_list_page' => env('EMBY_SERVER_MOVIE_LIST_PAGE'),

    'series_list_page' => env('EMBY_SERVER_SERIES_LIST_PAGE'),

    'item_url' => env('EMBY_SERVER_ITEM_PAGE'),

    'image_url' => env('EMBY_API_IMAGE_URL'),

    'user_id' => env('EMBY_USER_ID'),

];
