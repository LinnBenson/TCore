<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class router_record extends mysqlBasics {
        protected $table = 'router_record';
        protected $guarded = [];
        public static $words = [
            'router' => [ 'comment' => '路由名称', 'type' => [ 'string' ], 'nullable' => true ],
            'type' => [ 'comment' => '访问类型', 'type' => [ 'string' ], 'nullable' => true ],
            'result' => [ 'comment' => '访问结果', 'type' => [ 'string' ] ],
            'target' => [ 'comment' => '访问目标', 'type' => [ 'text' ], 'nullable' => true ],
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ], 'nullable' => true ],
            'access_id' => [ 'comment' => '识别码', 'type' => [ 'string' ], 'nullable' => true ],
            'access_ip' => [ 'comment' => '访问 IP', 'type' => [ 'string' ], 'nullable' => true ],
            'access_ua' => [ 'comment' => '访问设备', 'type' => [ 'text' ], 'nullable' => true ]
        ];
        public static function setData() {}
    }
