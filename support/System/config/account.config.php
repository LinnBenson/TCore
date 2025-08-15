<?php
    /**
     * 账户配置信息
     */
    return [
        'login' => [
            'enable' => true,
            'maintain' => [ 86400, 2592000 ]
        ],
        'register' => [
            'enable' => true,
            'invite' => true
        ],
        'status' => [
            'ADMIN' => 10,
            'STAFF' => 1000,
            'AGENT' => 10000,
            'VIP' => 100000,
            'USER' => 500000,
            'VISITOR' => 10000000
        ],
        'verify' => [
            'email' => true,
            'phone' => false,
        ]
    ];