<?php
namespace application\model;

use support\transfer\mysqlBasics;

    class admin_menu extends mysqlBasics {
        protected $table = 'admin_menu';
        protected $guarded = [];
        public static $words = [
            'uid' => [ 'comment' => 'UID', 'type' => [ 'integer' ], 'default' => 0 ],
            'menu' => [ 'comment' => '菜单分类', 'type' => [ 'string', 100 ] ],
            'type' => [ 'comment' => '类型', 'type' => [ 'string', 100 ], 'nullable' => true ],
            'title' => [ 'comment' => '名称', 'type' => [ 'string' ], 'nullable' => true ],
            'icon' => [ 'comment' => '图标', 'type' => [ 'string', 100 ], 'default' => 'bi-star' ],
            'target' => [ 'comment' => '链接', 'type' => [ 'string' ], 'nullable' => true ],
            'level' => [ 'comment' => '级别', 'type' => [ 'integer' ], 'default' => 600 ],
            'superior' => [ 'comment' => '上级', 'type' => [ 'string', 100 ], 'nullable' => true ],
            'enable' => [ 'comment' => '可用性', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 1 ],
            'sequence' => [ 'comment' => '排序', 'type' => [ 'integer' ], 'nullable' => true, 'default' => 1 ]
        ];
        public static function setData() {
            $set = [
                [
                    'menu' => 'main', 'type' => 'link', 'sequence' => 10,
                    'title' => '{{page./home}}', 'icon' => 'bi-house', 'target' => '/home'
                ],[
                    'menu' => 'main', 'type' => 'list', 'level'=> 600, 'sequence' => 100,
                    'title' => '{{page.user}}', 'icon' => 'bi-person-circle', 'target' => 'user'
                ],
                    [
                        'menu' => 'main', 'type' => 'link', 'level'=> 600, 'superior' => 'user', 'sequence' => 101,
                        'title' => '{{page./user/list}}', 'icon' => 'bi-people', 'target' => '/user/list'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 600, 'superior' => 'user', 'sequence' => 102,
                        'title' => '{{page./user/login}}', 'icon' => 'bi-person-plus', 'target' => '/user/login'
                    ],
                [
                    'menu' => 'main', 'type' => 'list', 'level'=> 600, 'sequence' => 200,
                    'title' => '{{page.article}}', 'icon' => 'bi-file-earmark-text', 'target' => 'article'
                ],
                    [
                        'menu' => 'main', 'type' => 'link', 'level'=> 600, 'superior' => 'article', 'sequence' => 202,
                        'title' => '{{page./article/new}}', 'icon' => 'bi-patch-plus', 'target' => '/article/new'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 600, 'superior' => 'article', 'sequence' => 202,
                        'title' => '{{page./article/list}}', 'icon' => 'bi-file-earmark-medical', 'target' => '/article/list'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 600, 'superior' => 'article', 'sequence' => 203,
                        'title' => '{{page./article/sort}}', 'icon' => 'bi-filter-left', 'target' => '/article/sort'
                    ],
                [
                    'menu' => 'main', 'type' => 'link', 'level'=> 600, 'sequence' => 900,
                    'title' => '{{page./access}}', 'icon' => 'bi-shield-exclamation', 'target' => '/access'
                ],[
                    'menu' => 'main', 'type' => 'list', 'level'=> 1000, 'sequence' => 1000,
                    'title' => '{{page.media}}', 'icon' => 'bi-folder', 'target' => 'media'
                ],
                    [
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'media', 'sequence' => 1001,
                        'title' => '{{page./media/user}}', 'icon' => 'bi-file-earmark-person', 'target' => '/media/user'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'media', 'sequence' => 1002,
                        'title' => '{{page./media/file}}', 'icon' => 'bi-files-alt', 'target' => '/media/file'
                    ],
                [
                    'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'sequence' => 1000,
                    'title' => '{{page./push}}', 'icon' => 'bi-send', 'target' => '/push'
                ],[
                    'menu' => 'main', 'type' => 'list', 'level'=> 1000, 'sequence' => 8000,
                    'title' => '{{page.setting}}', 'icon' => 'bi-gear', 'target' => 'setting'
                ],
                    [
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'setting', 'sequence' => 9001,
                        'title' => '{{page./setting/common}}', 'icon' => 'bi-grid-1x2', 'target' => '/setting/common'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'setting', 'sequence' => 9001,
                        'title' => '{{page./setting/theme}}', 'icon' => 'bi-palette', 'target' => '/setting/theme'
                    ],
                [
                    'menu' => 'main', 'type' => 'list', 'level'=> 1000, 'sequence' => 9000,
                    'title' => '{{page.dev}}', 'icon' => 'bi-bug', 'target' => 'dev'
                ],
                    [
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'dev', 'sequence' => 9001,
                        'title' => '{{page./service}}', 'icon' => 'bi-hdd-network', 'target' => '/service'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'dev', 'sequence' => 9002,
                        'title' => '{{page./dev/icon}}', 'icon' => 'bi-suit-heart', 'target' => '/dev/icon'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'dev', 'sequence' => 9003,
                        'title' => '{{page./dev/api}}', 'icon' => 'bi-puzzle', 'target' => '/dev/api'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'dev', 'sequence' => 9004,
                        'title' => '{{page./dev/log}}', 'icon' => 'bi-file-earmark', 'target' => '/dev/log'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'dev', 'sequence' => 9005,
                        'title' => '{{page./dev/info}}', 'icon' => 'bi-file-earmark-text', 'target' => '/dev/info'
                    ],[
                        'menu' => 'main', 'type' => 'link', 'level'=> 1000, 'superior' => 'dev', 'sequence' => 9006,
                        'title' => '{{page./about}}', 'icon' => 'bi-star', 'target' => '/about'
                    ],
                [
                    'menu' => 'main', 'type' => 'click', 'sequence' => 10000,
                    'title' => '{{page.logout}}', 'icon' => 'bi-box-arrow-left', 'target' => 'c.logout()'
                ]
            ];
            foreach( $set as $item ) { self::forceCreate( $item ); }
        }
    }
