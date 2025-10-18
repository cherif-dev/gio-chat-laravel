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
];