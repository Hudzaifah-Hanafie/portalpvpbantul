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

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],
    'hcaptcha' => [
        'site_key' => env('HCAPTCHA_SITE_KEY'),
        'secret_key' => env('HCAPTCHA_SECRET_KEY'),
    ],
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'siapkerja' => [
        'client_id' => env('SIAPKERJA_CLIENT_ID'),
        'client_secret' => env('SIAPKERJA_CLIENT_SECRET'),
        'redirect' => env('SIAPKERJA_REDIRECT_URI'),
        'scope' => env('SIAPKERJA_SCOPE', 'basic email'),
        'api_base' => env('SIAPKERJA_API_BASE', 'https://skillhub.kemnaker.go.id/api'),
        'service_base' => env('SIAPKERJA_SERVICE_BASE', 'https://data.kemnaker.go.id/api/v1/services'),
        'service_token' => env('SIAPKERJA_SERVICE_TOKEN'),
        'service_program_id' => env('SIAPKERJA_SERVICE_PROGRAM_ID'),
        'service_schedule_id' => env('SIAPKERJA_SERVICE_SCHEDULE_ID'),
        'service_instructor_id' => env('SIAPKERJA_SERVICE_INSTRUCTOR_ID'),
        'service_participant_id' => env('SIAPKERJA_SERVICE_PARTICIPANT_ID'),
        'service_pencaker_id' => env('SIAPKERJA_SERVICE_PENCAKER_ID'),
        'service_city_code' => env('SIAPKERJA_SERVICE_CITY_CODE'),
        'service_prov_code' => env('SIAPKERJA_SERVICE_PROV_CODE'),
        'service_vocational' => env('SIAPKERJA_SERVICE_VOCATIONAL'),
        'service_sub_vocational' => env('SIAPKERJA_SERVICE_SUB_VOCATIONAL'),
        'profile_api_base' => env('SIAPKERJA_PROFILE_API_BASE', 'https://api.kemnaker.go.id/profile'),
        'token_url' => env('SIAPKERJA_TOKEN_URL', 'https://account.kemnaker.go.id/api/v1/tokens'),
        'profile_url' => env('SIAPKERJA_PROFILE_URL', 'https://account.kemnaker.go.id/api/v1/users/me'),
        'admin_client_id' => env('SIAPKERJA_ADMIN_CLIENT_ID', env('SIAPKERJA_CLIENT_ID')),
        'admin_client_secret' => env('SIAPKERJA_ADMIN_CLIENT_SECRET', env('SIAPKERJA_CLIENT_SECRET')),
        'admin_scope' => env('SIAPKERJA_ADMIN_SCOPE', 'client'),
    ],

];
