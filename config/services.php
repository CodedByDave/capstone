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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'demo_enabled' => env('PAYMONGO_DEMO_ENABLED', env('APP_ENV', 'production') !== 'production'),
        'demo_secret_key' => env('PAYMONGO_DEMO_SECRET_KEY', 'sk_test_demoLaundryHubPanel2026'),
        'demo_public_key' => env('PAYMONGO_DEMO_PUBLIC_KEY', 'pk_test_demoLaundryHubPanel2026'),
    ],
    // Public OSRM routing server used for delivery road distances (no API key).
    // Point OSRM_URL at a self-hosted instance for production traffic.
    'osrm' => [
        'url' => env('OSRM_URL', 'https://router.project-osrm.org'),
        'timeout' => (int) env('OSRM_TIMEOUT', 4),
        'cache_ttl' => (int) env('OSRM_CACHE_TTL', 86400),
    ],
    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
];
