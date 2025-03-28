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
            'public/library/css/global.css',
            'public/library/font/Consolas-Regular.ttf',
            'public/library/icon/bootstrap/bootstrap-icons.min.css',
            'public/library/icon/bootstrap/bootstrap-icons.woff',
            'public/library/icon/bootstrap/bootstrap-icons.woff2',
            'public/library/js/clipboard_1.6.1.js',
            'public/library/js/cookie_1.4.1.js',
            'public/library/js/jquery-3.7.1.min.js',
            'public/library/js/marked.min.js',
            'public/library/js/core.js',
            'storage/backup/readme/TCoreView.md',
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
            'app/Controller/.gitignore',
            'app/Model/.gitignore',
            'app/Service/.gitignore',
            'app/Task/.gitignore',
            'public/assets/.gitignore',
            'resource/lang/.gitignore',
            'resource/view/.gitignore',
            'storage/cache/.gitignore',
            'storage/log/.gitignore',
            'app/Bootstrap/MainProcess.php',
            'config/autoload.config.php',
            'config/log.config.php',
            'config/router.config.php',
            'config/view.config.php',
            'public/library/icon/favicon.png',
            'public/library/icon/logo_b.png',
            'public/library/icon/logo_w.png',
            'public/favicon.ico',
        ]
    ];