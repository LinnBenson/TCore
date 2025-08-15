<?php
    /**
     * 数据库配置
     */
    return [
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
            'prefix' => strtolower( env( 'RE_PREFIX', env( 'APP_NAME', '' ) ) ),
            'host' => env( 'RE_HOST', 'localhost' ),
            'number' => env( 'RE_NUMBER', 0 ),
            'port' => env( 'RE_PORT', 6379 ),
            'password' => env( 'RE_PASSWORD', '' ),
        ],
        'session' => [
            'name' => env( 'SE_NAME', config( 'app.name' ).'_Session' ),
            'folder' => env( 'SE_FOLDER', 'storage/cache/Session' ),
            'expired' => env( 'SE_EXPIRED', 2592000 ),
        ]
    ];