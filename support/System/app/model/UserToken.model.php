<?php

    namespace App\Model;

    use Support\Slots\Mysql;
    use Support\Slots\MysqlGlobal;

    /**
     * 登录管理表
     */
    class UserToken extends Mysql {
        /**
         * 表信息
         */
        protected $table = 'user_token';
        public static $name = 'user_token|用户登录密钥';
        public static $info = [
            'uid|用户' => 'type:uid',
            'device|登录设备' => 'type:uuid|null',
            'token|密钥' => 'type:text',
            'enable|可用性' => 'type:boolean',
            'remember|记住状态' => 'type:boolean',
        ];
        /**
         * 允许批量赋值
         */
        protected $fillable = [ 'uid', 'device', 'token', 'enable', 'remember' ];
        /**
         * 数组序列化时隐藏内容
         */
        protected $hidden = [ 'token' ];
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