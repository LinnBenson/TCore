<?php
    /**
     * 存储器配置
     */
    return [
        /**
         * 缓存存储器
         */
        'cache' => [
            'type' => 'public',
            'path' => 'storage/media/cache/',
            'allow' => [
                'jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp', 'mp4', 'avi', 'mov',
                'mkv', 'mp3', 'wav', 'txt', 'doc', 'docx', 'pdf', 'zip', 'rar',
                'tar', 'gz', '7z', 'raw', 'psd', 'ai', 'eps', 'svg', 'json', 'xml',
                'csv', 'xls', 'xlsx', 'ppt', 'pptx', 'log', 'md', 'html', 'htm'
            ],
            'maxSize' => 1024 * 1024 * 10,
            'delete' => 3 * 24 * 60 * 60
        ]
    ];