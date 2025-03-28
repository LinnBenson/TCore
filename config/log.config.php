<?php
    return [
        'Bootstrap' => [
            'path' => 'storage/log/',
            'file' => 'Bootstrap.log',
            'maxSize' => 1024 * 1024 * 3,
        ],
        'Debug' => [
            'path' => 'storage/log/debug/',
            'file' => 'Debug_{{date}}.log',
            'maxSize' => 1024 * 1024 * 10,
        ],
        'Router' => [
            'path' => 'storage/log/',
            'file' => 'Router.log',
            'maxSize' => 1024 * 1024 * 3,
        ]
    ];