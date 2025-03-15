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
            'storage/log/.gitignore',
            'update'
        ],
        'dir' => [
            'support/'
        ],
        'delete' => [
            'file' => [

            ],
            'dir' => [

            ]
        ],
        'must' => [
            'app/Bootstrap/MainProcess.php',
            'config/app.config.php',
            'config/autoload.config.php',
            'config/log.config.php'
        ]
    ];