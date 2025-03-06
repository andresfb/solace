<?php

return [

    'driver' => 'mysql',
    'url' => env('MEDIA_RUNNER_DB_URL'),
    'read' => [
        'host' => [
            env('MEDIA_RUNNER_DB_HOST_READ', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => [
            env('MEDIA_RUNNER_DB_HOST_WRITE', '127.0.0.1'),
        ],
    ],
    'sticky' => true,
    'port' => env('MEDIA_RUNNER_DB_PORT', '3306'),
    'database' => env('MEDIA_RUNNER_DB_DATABASE', 'laravel'),
    'username' => env('MEDIA_RUNNER_DB_USERNAME', 'root'),
    'password' => env('MEDIA_RUNNER_DB_PASSWORD', ''),
    'unix_socket' => env('MEDIA_RUNNER_DB_SOCKET', ''),
    'charset' => env('MEDIA_RUNNER_DB_CHARSET', 'utf8mb4'),
    'collation' => env('MEDIA_RUNNER_DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MEDIA_RUNNER_MYSQL_ATTR_SSL_CA'),
    ]) : [],

];
