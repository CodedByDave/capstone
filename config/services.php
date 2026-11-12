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
    // Mapbox powers geocoding, road routing and route optimisation. The server-side proxy uses
    // MAPBOX_SECRET_TOKEN when set and otherwise falls back to the public (pk.) token, which the
    // Geocoding, Directions and Optimization APIs accept. The browser only ever gets the public token.
    'mapbox' => [
        'token' => env('MAPBOX_SECRET_TOKEN') ?: env('VITE_MAPBOX_TOKEN'),
        // The public (pk.) token handed to the browser at runtime, so it is never built into the committed assets.
        'public_token' => env('VITE_MAPBOX_TOKEN'),
        'base_url' => env('MAPBOX_BASE_URL', 'https://api.mapbox.com'),
        'timeout' => (int) env('MAPBOX_TIMEOUT', 6),
        'geocode_ttl' => (int) env('MAPBOX_GEOCODE_TTL', 86400),
        'route_ttl' => (int) env('MAPBOX_ROUTE_TTL', 3600),
    ],
    // OCR.space reads KYC permits to pre-fill expiry dates. Leave the key empty to disable.
    'ocr_space' => [
        'key' => env('OCR_SPACE_API_KEY'),
        'url' => env('OCR_SPACE_URL', 'https://api.ocr.space/parse/image'),
        'engine' => (int) env('OCR_SPACE_ENGINE', 2),
        'timeout' => (int) env('OCR_SPACE_TIMEOUT', 30),
    ],
    // Which service texts customers for every shop: "textbee" (free, an Android phone) or "philsms" (prepaid).
    'sms' => [
        'driver' => env('SMS_DRIVER', 'textbee'),
    ],
    // PhilSMS (philsms.com): Philippine SMS API, about P0.35 per text from prepaid credit.
    'philsms' => [
        'token' => env('PHILSMS_API_TOKEN'),
        // Up to 11 characters; a custom name must be approved by PhilSMS first.
        'sender_id' => env('PHILSMS_SENDER_ID', 'PhilSMS'),
        'base_url' => env('PHILSMS_BASE_URL', 'https://app.philsms.com/api/v3'),
        'timeout' => (int) env('PHILSMS_TIMEOUT', 15),
    ],
    // TextBee (textbee.dev) texts customers for every shop, from one Android phone the platform runs.
    // Leave the key empty to turn platform texting off; staff can still text from their own phone.
    'textbee' => [
        'key' => env('TEXTBEE_API_KEY'),
        'base_url' => env('TEXTBEE_BASE_URL', 'https://api.textbee.dev/api/v1'),
        // Optional: which phone sends. Empty uses the account's default phone.
        'device_id' => env('TEXTBEE_DEVICE_ID'),
        'timeout' => (int) env('TEXTBEE_TIMEOUT', 15),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
];
