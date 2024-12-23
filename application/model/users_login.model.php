<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class users_login extends mysqlBasics {
        protected $table = 'users_login';
        protected $guarded = [];
        public static $words = [
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ] ],
            'type' => [ 'comment' => '登录渠道', 'type' => [ 'string' ] ],
            'token' => [ 'comment' => 'Token', 'type' => [ 'text' ], 'nullable' => true ],
            'auth' => [ 'comment' => '验证', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'enable' => [ 'comment' => '可用性', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'expired' => [ 'comment' => '过期时间', 'type' => [ 'time' ], 'nullable' => true ],
            'expired_time' => [ 'comment' => '保持时间', 'type' => [ 'unsignedInteger' ], 'default' => 0 ],
            'login_id' => [ 'comment' => '识别码', 'type' => [ 'string' ], 'nullable' => true ],
            'login_ip' => [ 'comment' => '操作 IP', 'type' => [ 'string' ], 'nullable' => true ],
            'login_device' => [ 'comment' => '操作设备', 'type' => [ 'text' ], 'nullable' => true ]
        ];
        public static function setData() {}
    }
