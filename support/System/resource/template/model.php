<?php

namespace App\Model;

use Support\Slots\Mysql;
use Support\Slots\MysqlGlobal;

    class {{name}} extends Mysql {
        /**
         * 表信息
         */
        protected $table = 'xxx';
        public static $name = 'xxx|xxx';
        public static $info = [
            'uid'       => 'type:uid',
            'string'    => 'type:string|length:10',
            'int'       => 'type:number|length:4',
            'text'      => 'type:text|null',
        ];
        /**
         * 允许批量赋值
         */
        protected $fillable = [];
        // protected $guarded = [];
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