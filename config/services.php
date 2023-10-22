<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'newsapi' => [
        'base_url' => env('NEWSAPI_BASE_URL'),
        'api_key' => env('NEWSAPI_API_KEY'),
    ],
    'newsdataio' => [
        'base_url' => env('NEWSDATAIO_BASE_URL'),
        'api_key' => env('NEWSDATAIO_API_KEY'),
    ],
    'theguardian' => [
        'base_url' => env('THEGUARDIAN_BASE_URL'),
        'api_key' => env('THEGUARDIAN_API_KEY'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
