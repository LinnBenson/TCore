<?php
namespace application\model;

use core;
use support\transfer\mysqlBasics;
use task;

    class users extends mysqlBasics {
        protected $table = 'users';
        protected $guarded = [];
        public static $words = [
            'username' => [ 'comment' => '用户名', 'type' => [ 'string', 100 ], 'unique' => true ],
            'email' => [ 'comment' => '邮箱', 'type' => [ 'string' ], 'unique' => true ],
            'phone' => [ 'comment' => '手机号', 'type' => [ 'string', 100 ], 'nullable' => true ],
            'nickname' => [ 'comment' => '昵称', 'type' => [ 'string' ] ],
            'status' => [ 'comment' => '身份', 'type' => [ 'string' ] ],
            'slogan' => [ 'comment' => '签名', 'type' => [ 'string' ], 'nullable' => true ],
            'enable' => [ 'comment' => '可用性', 'type' => [ 'boolean' ], 'nullable' => true, 'default' => 0 ],
            'password' => [ 'comment' => '密码', 'type' => [ 'string' ] ],
            'invite' => [ 'comment' => '邀请码', 'type' => [ 'string', 15 ], 'nullable' => true ],
            'agent' => [ 'comment' => '上级代理', 'type' => [ 'integer' ], 'nullable' => true ],
            'agent_node' => [ 'comment' => '代理节点', 'type' => [ 'string' ], 'nullable' => true ],
            'register_ip' => [ 'comment' => '注册 IP', 'type' => [ 'string' ], 'nullable' => true ],
            'register_device' => [ 'comment' => '注册设备', 'type' => [ 'text' ], 'nullable' => true ]
        ];
        public static function setData() {
            self::forceCreate([
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'phone' => '',
                'nickname' => 'Administrator',
                'slogan' => 'System generated account.',
                'status' => 'admin',
                'enable' => '1',
                'password' => task::$user->setPassword( 'admin' ),
                'invite' => '10001000',
                'agent_node' => '|'
            ]);
            core::$db::statement( "ALTER TABLE ".config( 'database.mysql.prefix' )."users AUTO_INCREMENT = 60280" );
        }
    }
