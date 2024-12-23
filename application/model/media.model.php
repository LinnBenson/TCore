<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class media extends mysqlBasics {
        protected $table = 'media';
        protected $guarded = [];
        public static $words = [
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ] ],
            'storage' => [ 'comment' => '存储器', 'type' => [ 'string' ] ],
            'file' => [ 'comment' => '文件名', 'type' => [ 'string' ] ],
            'public' => [ 'comment' => '公开性', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'application' => [ 'comment' => '用途', 'type' => [ 'string' ], 'nullable' => true ]
        ];
        public static function setData() {}
    }
