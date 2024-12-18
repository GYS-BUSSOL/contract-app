<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => explode(',', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,DELETE,PATCH')),

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:8000,http://127.0.0.1:8000')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => explode(',', env('CORS_ALLOWED_HEADERS', 'Content-Type, X-Requested-With, Authorization')),

    'exposed_headers' => explode(',', env('CORS_EXPOSED_HEADERS', '')),

    'max_age' => env('CORS_MAX_AGE', 0),

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];
