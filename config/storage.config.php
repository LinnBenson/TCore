<?php
    return [
        'cache' => [
            'type' => 'public',
            'path' => '/storage/media/cache',
            'allow' => '*',
            'maxSize' => 1024 * 1024 * 10,
            'delete' => 3 * 24 * 60 * 60
        ],
    ];