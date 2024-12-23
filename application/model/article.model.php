<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class article extends mysqlBasics {
        protected $table = 'article';
        protected $guarded = [];
        public static $words = [
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ] ],
            'type' => [ 'comment' => '文章类型', 'type' => [ 'string' ] ],
            'title' => [ 'comment' => '文章名称', 'type' => [ 'string' ] ],
            'sort' => [ 'comment' => '分类', 'type' => [ 'integer' ], 'default' => 1 ],
            'public' => [ 'comment' => '公开性', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'release' => [ 'comment' => '已发布', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'synopsis' => [ 'comment' => '介绍', 'type' => [ 'text' ], 'nullable' => true ],
            'content' => [ 'comment' => '内容', 'type' => [ 'longtext' ], 'nullable' => true ],
            'media' => [ 'comment' => '媒体内容', 'type' => [ 'json' ] ],
            'tag' => [ 'comment' => '文章标签', 'type' => [ 'text' ], 'nullable' => true ],
        ];
        public static function setData() {}
    }
