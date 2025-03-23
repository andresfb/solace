<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Base Options
    |--------------------------------------------------------------------------
    |
    | The basic set of option we want to send to OpenAI.
    */

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini-2024-07-18'),

    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 768),

    'presence_penalty' => (float) env('OPENAI_PRESENCE_PENALTY', 0.3),

];
