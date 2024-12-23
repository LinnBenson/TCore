<?php
    return array (
        'mysql' => [
            'driver' => 'mysql',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env( 'DB_PREFIX', '' ),
            'host' => env( 'DB_HOST', 'localhost' ),
            'port' => env( 'DB_PORT', 3306 ),
            'database' => env( 'DB_DATABASE', '' ),
            'username' => env( 'DB_USERNAME', '' ),
            'password' => env( 'DB_PASSWORD', '' ),
        ],
        'redis' => [
            'prefix' => env( 'RE_PREFIX', '' ),
            'host' => env( 'RE_HOST', 'localhost' ),
            'port' => env( 'RE_PORT', 6379 ),
            'password' => env( 'RE_PASSWORD', '' ),
        ]
    );