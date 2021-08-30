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

    'paths' => ['*'],

    'allowed_methods' => [
        'GET,HEAD,PUT,POST,DELETE,PATCH,OPTIONS',
    ],

    'allowed_origins' => [
        "http://localhost:8080", //localhost,
        "http://192.168.1.24:8080", //localhost
        "http://pos-live.beliayam.test", //localhost,
        "http://pos.beliayam.test", // non SSL production
        "http://pos.beliayam.com", // non SSL production
        "https://pos.beliayam.com", // SSL Production
        "https://pos-admin.beliayam.com" // SSL Production
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
