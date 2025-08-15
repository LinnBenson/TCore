<?php

    namespace App\Model;

    use Support\Slots\Mysql;
    use Support\Slots\MysqlGlobal;

    /**
     * {{name}} 数据模型
     * 类型 : string|字符串, uid|用户ID, number|数字, boolean|布尔, float|浮点数, double|双精度, decimal|十进制, text|文本, longtext|长文本, json|JSON, timestamp|时间戳
     * 其它 : default|默认值, null|可空, only|唯一, length|长度
     */
    class {{name}} extends Mysql {
        /**
         * 表信息
         */
        protected $table = '{{table}}';
        public static $name = '{{table}}|{{name}}';
        public static $info = [
            'uid|用户' => 'type:uid',
            'enable|可用性' => 'type:boolean'
        ];
        /**
         * 允许批量赋值
         */
        protected $fillable = [];
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