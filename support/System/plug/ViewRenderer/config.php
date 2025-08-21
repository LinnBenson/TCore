<?php
    /**
     * 视图配置
     */
    return [
        // 主题定义
        'theme' => [
            'Default' => array (
                'logo' => '/library/logo_b.png',
                'fav' => '/library/favicon.png',
                'img' => '/library/welcome.jpg',
                '--r0' => '237, 236, 231',
                '--r1' => '70, 70, 70',
                '--r2' => '65, 115, 179',
                '--r2c' => 'var( --r0 )',
                '--r3' => '114, 141, 167',
                '--r3c' => 'var( --r0 )',
                '--r4' => '141, 178, 43',
                '--r4c' => 'var( --r0 )',
                '--r5' => '223, 92, 79',
                '--r5c' => 'var( --r0 )',
                '--r6' => '249, 247, 244',
            ),
            'Dark' => array (
                'logo' => '/library/logo_w.png',
                'fav' => '/library/favicon.png',
                'img' => '/library/welcome.jpg',
                '--r0' => '70, 70, 70',
                '--r1' => '237, 236, 231',
                '--r2' => '65, 115, 179',
                '--r2c' => 'var( --r1 )',
                '--r3' => '114, 141, 167',
                '--r3c' => 'var( --r1 )',
                '--r4' => '141, 178, 43',
                '--r4c' => 'var( --r1 )',
                '--r5' => '223, 92, 79',
                '--r5c' => 'var( --r1 )',
                '--r6' => '44, 44, 39',
            ),
        ],
        // 视图路径
        'path' => 'resource/view/'
    ];