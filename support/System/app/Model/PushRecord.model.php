<?php

namespace App\Model;

use Support\Slots\Mysql;
use Support\Slots\MysqlGlobal;

    class PushRecord extends Mysql {
        /**
         * 表信息
         */
        protected $table = 'push_record';
        public static $name = 'push_record|推送记录表';
        public static $info = [
            'uid|UID'       => 'type:uid',
            'type|推送途径' => 'type:string',
            'receive|接收者' => 'type:string',
            'title|标题' => 'type:text|null',
            'content|内容' => 'type:text|null',
            'source|来源' => 'type:string|null',
            'remark|备注' => 'type:text|null',
        ];
        /**
         * 允许批量赋值
         */
        protected $guarded = [];
        /**
         * 数组序列化时隐藏内容
         */
        protected $hidden = [];
        /**
         * 类型转换
         */
        protected $casts = [];
        /**
         * 全局作用域
         */
        protected static function boot() {
            parent::boot();
            static::addGlobalScope( new MysqlGlobal );
        }
        /**
         * 初始化表数据
         */
        public static function initialization() {
            $insert = [];
            foreach( $insert as $item ) { self::forceCreate( $item ); };
            return true;
        }
    }