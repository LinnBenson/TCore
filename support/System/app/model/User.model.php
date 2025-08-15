<?php
    namespace App\Model;

    use Support\Handler\Account;
    use Support\Slots\Mysql;
    use Support\Slots\MysqlGlobal;

    /**
     * 用户信息表
     */
    class User extends Mysql {
        /**
         * 表信息
         */
        protected $table = 'user';
        public static $name = 'user|用户信息表';
        public static $info = [
            'username|用户名' => 'type:string|length:20|only',
            'email|邮箱' => 'type:string|length:50|null|only',
            'phone|手机号' => 'type:string|length:20|null|only',
            'password|密码' => 'type:string',
            'nickname|昵称' => 'type:string|length:60',
            'level|级别' => 'type:number|length:4',
            'slogan|签名' => 'type:text|null',
            'enable|可用性' => 'type:boolean',
            'invite|邀请码' => 'type:string|length:10',
            'agent|上级代理' => 'type:uid',
            'agent_node|邀请节点' => 'type:text|null',
            'network|注册网络' => 'type:string|null',
            'device|注册设备' => 'type:uuid|null',
        ];
        /**
         * 允许批量赋值
         */
        protected $fillable = [
            'username', 'email', 'phone',
            'password', 'nickname', 'level',
            'slogan', 'enable', 'invite',
            'agent', 'agent_node', 'network',
            'device'
        ];
        /**
         * 数组序列化时隐藏内容
         */
        protected $hidden = [ 'password' ];
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
            // 禁用修改
            static::updating( function( $model ) {
                if ( $model->isDirty( 'device' ) ) { $model->device = $model->getOriginal( 'device' ); }
            });
        }
        /**
         * 初始化表数据
         */
        public static function initialization() {
            $insert = [
                [
                    'username' => 'admin',
                    'email' => null,
                    'phone' => null,
                    'password' => Account::password( '123456' ),
                    'nickname' => 'Administrator',
                    'level' => 1,
                    'slogan' => 'Administrator Account',
                    'enable' => true,
                    'invite' => '00000000',
                    'agent' => null,
                    'agent_node' => '|',
                    'network' => '0.0.0.0',
                    'device' => '00000000-0000-0000-0000-000000000000',
                ]
            ];
            foreach( $insert as $item ) { self::forceCreate( $item ); };
            return true;
        }
    }