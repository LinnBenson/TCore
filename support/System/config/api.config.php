<?php
    return [
        'service' => env( 'API_SERVICE', '' ),
        'email' => [
            'host' => env( 'API_EMAIL_HOST', '' ),
            'port' => env( 'API_EMAIL_PORT', 465 ),
            'username' => env( 'API_EMAIL_USERNAME', '' ),
            'password' => env( 'API_EMAIL_PASSWORD', '' ),
            'encryption' => env( 'API_EMAIL_ENCRYPTION', 'ssl' ),
            'receive' => env( 'API_EMAIL_RECEIVE', '' )
        ],
        'telegram' => [
            'key' => env( 'API_TELEGRAM_KEY', '' ),
            'receive' => env( 'API_TELEGRAM_RECEIVE', '' )
        ],
        'bark' => [
            'host' => env( 'API_BARK_HOST', 'https://api.day.app' ),
            'receive' => env( 'API_BARK_RECEIVE', '' )
        ]
    ];