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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
        // OAuth client IDs of the iOS / Android apps (comma-separated) — mobile
        // Google ID tokens must be issued to one of these (or the web client).
        'mobile_client_ids' => array_filter(explode(',', (string) env('GOOGLE_MOBILE_CLIENT_IDS', ''))),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),          // Services ID, e.g. com.sparxbit.web
        'client_secret' => env('APPLE_CLIENT_SECRET'),  // generated JWT, or left blank to build from the key below
        'redirect' => env('APPLE_REDIRECT_URI', env('APP_URL').'/auth/apple/callback'),
        // Used by socialiteproviders/apple to generate the client secret JWT:
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_KEY_ID'),
        'private_key' => env('APPLE_PRIVATE_KEY'),       // contents of the .p8 (or a path)
        // Native iOS Sign in with Apple tokens are issued to the app bundle ID.
        'audiences' => [env('APPLE_BUNDLE_ID', 'com.sparxbit.app')],
    ],

    'webpush' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', 'mailto:'.env('MAIL_FROM_ADDRESS', 'info@yourdeveloperonline.com')),
    ],

];
