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
        '*',
    ],

    'allowed_origins' => [
        "http://192.168.1.9", //localhost
        "http://localhost:8080", //localhost,
        //"https://localhost:8080", //localhost,
        "http://localhost:8081", //localhost
        //"https://localhost:8081", //localhost,
        "http://192.168.1.24:8080", //localhost
        "http://pos-live.beliayam.test", //localhost,
        "http://pos.beliayam.test", // non SSL production
        "https://pos-dev.beliayam.com",
        "http://pos.beliayam.com", // non SSL production
        "https://pos.beliayam.com", // SSL Production
        "https://pos-admin.beliayam.com", // SSL Production
        "http://beliayam.test", // website development
        "https://beliayam.com" // SSL Production
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
