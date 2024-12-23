<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class push_record extends mysqlBasics {
        protected $table = 'push_record';
        protected $guarded = [];
        public static $words = [
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ], 'nullable' => true ],
            'type' => [ 'comment' => '推送类型', 'type' => [ 'string' ] ],
            'to' => [ 'comment' => '接收者', 'type' => [ 'string' ] ],
            'title' => [ 'comment' => '标题', 'type' => [ 'text' ], 'nullable' => true ],
            'content' => [ 'comment' => '内容', 'type' => [ 'longtext' ], 'nullable' => true ],
            'source' => [ 'comment' => '来源', 'type' => [ 'string' ], 'nullable' => true ],
            'send_id' => [ 'comment' => '识别码', 'type' => [ 'string' ], 'nullable' => true ],
            'send_ip' => [ 'comment' => '操作 IP', 'type' => [ 'string' ], 'nullable' => true ]
        ];
        public static function setData() {}
    }
