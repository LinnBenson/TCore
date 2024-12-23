<?php
    return [
        'email' => [
            'default' => env( 'MAIL_DEFAULT', '' ),
            'driver' => env( 'MAIL_DRIVER', 'smtp' ),
            'host' => env( 'MAIL_HOST', '' ),
            'port' => env( 'MAIL_PORT', '465' ),
            'username' => env( 'MAIL_USERNAME', '' ),
            'password' => env( 'MAIL_PASSWORD', '' ),
            'from' => env( 'MAIL_FROM_ADDRESS', env( 'MAIL_USERNAME', '' ) ),
            'encrypt' => env( 'MAIL_ENCRYPTION', '' )
        ],
        'bark' => [
            'default' => env( 'BARK_DEFAULT', '' ),
            'host' => env( 'BARK_HOST', '' )
        ],
        'telegram' => [
            'default' => env( 'TELEGRAM_DEFAULT', '' ),
            'host' => env( 'TELEGRAM_HOST', '' )
        ]
    ];