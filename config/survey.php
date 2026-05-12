<?php

return [
    'captcha_provider' => env('SURVEY_CAPTCHA_PROVIDER', 'recaptcha'),
    'file_max_mb' => env('SURVEY_FILE_MAX_MB', 20),
    'file_mimes' => env('SURVEY_FILE_MIMES', 'jpeg,png,pdf,doc,docx'),

    'webhooks' => [
        'slack' => env('SURVEY_WEBHOOK_SLACK'),
        'sheets' => env('SURVEY_WEBHOOK_SHEETS'),
        'attachment_scan' => env('SURVEY_ATTACHMENT_SCAN_WEBHOOK'),
    ],

    'telegram' => [
        'bot_token' => env('SURVEY_TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('SURVEY_TELEGRAM_CHAT_ID'),
    ],
];
