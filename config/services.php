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

    'openrouter' => [
        'key'   => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'google/gemini-1.5-flash'),
    ],

    'shopify' => [
        'client_id'     => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
    ],

    'woocommerce' => [
        'app_name'     => env('WOOCOMMERCE_APP_NAME'),
        'callback_url' => env('WOOCOMMERCE_CALLBACK_URL'),
    ],

    'tiktok_shop' => [
        'app_key'    => env('TIKTOK_SHOP_APP_KEY'),
        'app_secret' => env('TIKTOK_SHOP_APP_SECRET'),
    ],

    'shopee' => [
        'partner_id'  => env('SHOPEE_PARTNER_ID'),
        'partner_key' => env('SHOPEE_PARTNER_KEY'),
    ],

    'gofood' => [
        'merchant_id' => env('GOFOOD_MERCHANT_ID'),
        'api_key'     => env('GOFOOD_API_KEY'),
        'api_secret'  => env('GOFOOD_API_SECRET'),
    ],

    'grabfood' => [
        'client_id'     => env('GRABFOOD_CLIENT_ID'),
        'client_secret' => env('GRABFOOD_CLIENT_SECRET'),
    ],

    'turnstile' => [
        'site_key'   => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

];
