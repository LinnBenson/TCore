<?php
    return [
        'cache' => [
            'public' => true,
            'dir' => '/cache',
            'size' => [ 0, 10000000 ],
            'allow' => false
        ],
        'avatar' => [
            'public' => true,
            'dir' => '/avatar',
            'size' => [ 0, 3000000 ],
            'allow' => [ 'png', 'jpg', 'jpeg' ]
        ],
        'word' => [
            'public' => false,
            'dir' => '/word',
            'size' => [ 0, 10000000 ],
            'allow' => [ 'png', 'jpg', 'jpeg', 'gif' ]
        ]
    ];