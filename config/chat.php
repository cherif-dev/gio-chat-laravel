<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model that the package should use for relationships.
    |
    */
    'user_model' => env('CHAT_USER_MODEL', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the package.
    |
    */
    'tables' => [
        'genit_io_chats' => 'genit_io_chats',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for messages.
    |
    */
    'pagination' => [
        'default_limit' => 100,
        'max_limit' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Genit.io API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for communicating with the genit.io microservice.
    |
    */
    'base_url' => env('GENIT_IO_API_BASE_URL', 'https://gio-admin.test'),
    'project' => env('GENIT_IO_PROJECT'),
    'project_api_key' => env('GENIT_IO_PROJECT_API_KEY'),
    'timeout' => env('GENIT_IO_API_TIMEOUT', 30), // seconds
    'retry_times' => env('GENIT_IO_API_RETRY_TIMES', 3),
    'retry_delay' => env('GENIT_IO_API_RETRY_DELAY', 100), // milliseconds
    'verify_ssl' => env('GENIT_IO_API_VERIFY_SSL', true),
];
