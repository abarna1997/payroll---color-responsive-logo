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

    'holiday_api' => [
        'url' => env('HOLIDAY_API_URL', 'https://induwara.lk/api/v1/holidays'),
        'key' => env('HOLIDAY_API_KEY'),
    ],

    'wso2' => [
        'client_id'     => env('WSO2_IS_CLIENT_ID'),
        'client_secret' => env('WSO2_IS_CLIENT_SECRET'),
        'redirect'      => env('WSO2_IS_REDIRECT_URI'),
        'base_url'      => env('WSO2_IS_BASE_URL', 'https://localhost:9443'),
        'verify_ssl'    => filter_var(env('WSO2_IS_VERIFY_SSL', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
