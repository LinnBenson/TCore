<?php

    namespace App\Model;

    use Support\Slots\Mysql;
    use Support\Slots\MysqlGlobal;

    /**
     * AdminMenu 数据模型
     * 类型 : string|字符串, uid|用户ID, number|数字, boolean|布尔, float|浮点数, double|双精度, decimal|十进制, text|文本, longtext|长文本, json|JSON, timestamp|时间戳
     * 其它 : default|默认值, null|可空, only|唯一, length|长度
     */
    class AdminMenu extends Mysql {
        /**
         * 表信息
         */
        protected $table = 'admin_menu';
        public static $name = 'admin_menu|管理员菜单';
        public static $info = [
            'name|字段名' => 'type:string|length:20|null',
            'icon|图标' => 'type:string|length:20|null',
            'url|访问链接' => 'type:string|null',
            'parent|父级菜单' => 'type:string|null',
            'level|访问级别' => 'type:number',
            'serial|排序' => 'type:number',
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
            $insert = [
                [ 'name' => 'Dashboard', 'icon' => 'bi-house-door', 'url' => '#view=admin.dashboard', 'level' => 10000, 'parent' => '', 'serial' => 1, 'enable' => 1 ],
                [ 'name' => 'Develop', 'icon' => 'bi-bug', 'url' => '', 'parent' => '', 'level' => 10, 'serial' => 1, 'enable' => 1 ],
                [ 'name' => 'Api', 'icon' => 'bi-bounding-box', 'url' => '#view=admin.develop.api', 'parent' => 'Develop', 'level' => 10, 'serial' => 1, 'enable' => 1 ],
                [ 'name' => 'Bootstrap Icons', 'icon' => 'bi-star', 'url' => '#view=admin.develop.icons', 'parent' => 'Develop', 'level' => 10, 'serial' => 1, 'enable' => 1 ],
                [ 'name' => 'Module', 'icon' => 'bi-columns-gap', 'url' => '#view=admin.develop.module', 'parent' => 'Develop', 'level' => 10, 'serial' => 1, 'enable' => 1 ],
                [ 'name' => 'Handbook', 'icon' => 'bi-book', 'url' => '#view=admin.develop.handbook', 'parent' => 'Develop', 'level' => 10, 'serial' => 1, 'enable' => 1 ],
                [ 'name' => 'Log', 'icon' => 'bi-receipt', 'url' => '#view=admin.develop.log', 'parent' => 'Develop', 'level' => 10, 'serial' => 1, 'enable' => 1 ]
            ];
            foreach( $insert as $item ) { self::forceCreate( $item ); };
            return true;
        }
    }