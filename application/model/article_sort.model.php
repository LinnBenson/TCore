<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class article_sort extends mysqlBasics {
        protected $table = 'article_sort';
        protected $guarded = [];
        public static $words = [
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ], 'default' => 0 ],
            'public' => [ 'comment' => '公开性', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'name' => [ 'comment' => '分类名称', 'type' => [ 'string' ], 'unique' => true ],
            'posts' => [ 'comment' => '允许投稿', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
        ];
        public static function setData() {
            $set = [
                [ 'uid' => 0, 'public' => 0, 'name' => 'Draft', 'posts' => 1 ],
                [ 'uid' => 0, 'public' => 1, 'name' => 'System', 'posts' => 0 ],
                [ 'uid' => 0, 'public' => 1, 'name' => 'Notify', 'posts' => 0 ]
            ];
            foreach( $set as $item ) { self::forceCreate( $item ); }
        }
    }
