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

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    'stripe' => [
        'key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret' => env('STRIPE_SECRET_KEY') ?: (env('STRIPE_SECRET_KEY_B64') ? base64_decode((string) env('STRIPE_SECRET_KEY_B64')) : null),
    ],

    'gateway' => [
        'public_key' => env('GATEWAY_PUBLIC_KEY', 'PAY_PK_F657F32F98E0E45C9BEAE0EAE014CF4E'),
        'secret_key' => env('GATEWAY_SECRET_KEY', 'PAY_SK_91930A5CF36509B1834482D87E2EB3237DD383A6CD0E7AA5'),
        'url' => env('GATEWAY_API_URL', 'https://passerellepaiement.wasmer.app/api/v1'),
    ],

];
