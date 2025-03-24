<?php

return [

    'driver' => 'mysql',
    'url' => env('NEWS_FEED_RUNNER_DB_URL'),
    'read' => [
        'host' => [
            env('NEWS_FEED_RUNNER_DB_HOST_READ', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => [
            env('NEWS_FEED_RUNNER_DB_HOST_WRITE', '127.0.0.1'),
        ],
    ],
    'sticky' => true,
    'port' => env('NEWS_FEED_RUNNER_DB_PORT', '3306'),
    'database' => env('NEWS_FEED_RUNNER_DB_DATABASE', 'laravel'),
    'username' => env('NEWS_FEED_RUNNER_DB_USERNAME', 'root'),
    'password' => env('NEWS_FEED_RUNNER_DB_PASSWORD', ''),
    'unix_socket' => env('NEWS_FEED_RUNNER_DB_SOCKET', ''),
    'charset' => env('NEWS_FEED_RUNNER_DB_CHARSET', 'utf8mb4'),
    'collation' => env('NEWS_FEED_RUNNER_DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('NEWS_FEED_RUNNER_MYSQL_ATTR_SSL_CA'),
    ]) : [],

];
