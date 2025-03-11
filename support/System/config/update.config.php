<?php
    /**
     * 版本更新说明：
     * - 1.0.0 正式发布
     */
    return [
        'name' => 'TCore',
        'version' => '1.0.0',
        'git' => 'https://github.com/LinnBenson/TCore/archive/refs/heads/main.zip',
        'cache' => [
            'dir' => 'update_cache/',
            'file' => 'latest.zip'
        ],
        'file' => [
            'public/index.php',
            'support/Helper/global.helper.php',
            'support/System/config/update.config.php',
            '.env.example',
            '.gitignore',
            'LICENSE',
            'README.md',
            'update'
        ],
        'dir' => [

        ],
        'delete' => [
            'file' => [

            ],
            'dir' => [

            ]
        ],
        'composer' => [
            'vlucas/phpdotenv',
            'blocktrail/cryptojs-aes-php',
            'illuminate/database',
            'workerman/workerman',
            'workerman/channel',
            'textalk/websocket'
        ]
    ];