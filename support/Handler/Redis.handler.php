<?php
    /**
     * Redis 操作器
     */
    namespace Support\Handler;
    use Support\Bootstrap;

    class Redis {
        public static $ref = null; // Redis 连接
        /**
         * 日志记录
         */
        private static function log( $text ) {
            Bootstrap::log( 'Redis', $text );
            return false;
        }
        /**
         * 获取 Redis 连接
         * - 用于获取原生 Redis 连接
         * - @return object|null 连接
         */
        public static function link() {
            if ( self::register() === false ) { return null; }
            return self::$ref;
        }
        /**
         * 注册 Redis
         * - 用于初始化注册
         * - Redis::register()
         * - return boolean Redis 连接结果
         */
        public static function register() {
            // 扩展检查
            if ( !extension_loaded( 'redis' ) ) { return self::log( 'Redis is not installed.' ); }
            // 检查当前是否连接正常
            if ( !empty( $ref ) ) {
                // 简单查询检查连接
                try {
                    if ( self::$ref->ping() !== false ) { return true; }
                }catch ( PDOException $e ) {
                    return self::log( 'Redis failed while checking connection.' );
                }
            }
            try {
                // 尝试建立连接
                self::$ref = new \Redis();
                self::$ref->connect( config( 'database.redis.host' ), config( 'database.redis.port' ), 2.5 );
                if ( !empty( config( 'database.redis.password' ) ) ) { self::$ref->auth( config( 'database.redis.password' ) ); }
            }catch ( Throwable $e ) {
                return self::log( 'Redis connection failed.' );
            }
            return self::$ref->ping() !== false ? self::$ref->select( config( 'database.redis.number' ) ) : false;
        }
        /**
         * 切换 Redis 库
         * - 用于切换当前选择的库
         * - Redis::select( $number|0:number(库编号) )
         * - return boolean 切换结果
         */
        public static function select( $number = 0 ) {
            if ( !is_numeric( $number ) || self::register() === false ) { return false; }
            return self::$ref->select( $number );
        }
        /**
         * 手动关闭连接
         * - 用于关闭 Redis 连接
         * - Redis::close()
         * - return true 关闭结果
         */
        public static function close() {
            if ( !empty( self::$ref ) && self::register() === true ) { self::$ref->close(); }
            return true;
        }
        /**
         * 添加前缀
         * - 用于给键名加上指定头
         * - Redis::name( $text:string(字段名称) )
         * - return string 加工后的名称
         */
        public static function name( $text ) {
            $text = self::filter( $text );
            if ( substr( $text, 0, strlen( config( 'database.redis.prefix' ) ) ) === config( 'database.redis.prefix' ) ) { return $text; }
            return config( 'database.redis.prefix' ).'_'.$text;
        }
        /**
         * 过滤输入内容
         * - 格式化检查输入的内容
         * - Redis::filter( $text:any(输入内容) )
         * - return string 过滤结果
         */
        public static function filter( $text ) {
            if ( !is_json( $text ) && is_string( $text ) ) {
                return addslashes( str_replace( ["\0", "\r", "\n", "\x08", "\x0c", ":", "*", "?"], '', $text ) );
            }
            if ( is_array( $text ) ) { return json_encode( $text ); }
            if ( is_bool( $text ) ) { return $text ? '<true>' : '<false>'; }
            if ( $text === null ) { return '<null>'; }
            if ( is_numeric( $text ) ) { return $text; }
            return '';
        }
        /**
         * 提取输出内容
         * - 格式化处理输出的内容
         * - Redis::output( $result:string(输出内容) )
         * - return any 提取结果
         */
        public static function output( $result ) {
            if ( is_json( $result ) ) { return json_decode( $result, true ); }
            if ( $result === '<true>' || $result === '<false>' ) { return $result === '<true>' ? true : false; }
            if ( $result === '<null>' ) { return null; }
            return stripslashes( $result );
        }
        /**
         * 给一个值添加过期时间
         * - 用于给一个缓存添加过期时间
         * - Redis::expire( $key:string(键名), $expire|false:number(过期时间，秒) )
         * - return boolean 添加结果
         */
        public static function expire( $key, $expire = false ) {
            // 参数检查
            if (
                !is_string( $key ) ||
                ( !empty( $expire ) && !is_numeric( $expire ) ) ||
                self::register() === false
            ) { return false; }
            // 处理逻辑
            try {
                return self::$ref->expire( self::name( $key ), $expire ) ? true : false;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 查询缓存
         * - 用于查询一个缓存
         * - Redis::get( $key:string(键名) )
         * - return any 查询结果
         */
        public static function get( $key ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                return self::output( self::$ref->get( self::name( $key ) ) );
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 设置缓存
         * - 用于设定一个缓存
         * - Redis::set( $key:string(键名), $value:any(值), $expire|false:number(过期时间) )
         * - return boolean 设置结果
         */
        public static function set( $key, $value, $expire = false ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $name = self::name( $key );
                $run = self::$ref->set( $name, self::filter( $value ) );
                // 是否增加过期时间
                if ( is_numeric( $expire ) ) {
                    self::$ref->expire( $name, $expire );
                }
                // 返回设置结果
                return $run ? true : false;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 删除缓存
         * - 用于删除一个缓存
         * - Redis::del( $key:string(键名) )
         * - return number 删除数量
         */
        public static function del( $key ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $run = self::$ref->del( self::name( $key ) );
                return is_numeric( $run ) ? $run : 0;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 查询全部指定开头缓存
         * - 查询所有指定关键词开头的缓存
         * - Redis::getAll( $startKey:string(键名) )
         * - return array 查询结果
         */
        public static function getAll( $startKey ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $keys = self::$ref->keys( self::name( $startKey ).'*' );
                if ( !is_array( $keys ) ) { return []; }
                $data = array();
                foreach ( $keys as $key ) {
                    $data[$key] = self::output( self::$ref->get( self::name( $key ) ) );
                }
                return $data;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 删除全部指定开头缓存
         * - 删除所有指定关键词开头的缓存
         * - Redis::delAll( $startKey:string(键名) )
         * - return number 删除数量
         */
        public static function delAll( $startKey ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $keys = self::$ref->keys( self::name( $startKey ).'*' );
                if ( !empty( $keys ) ) {
                    $run = self::$ref->del( $keys );
                    return is_numeric( $run ) ? $run : 0;
                }
                return 0;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 向数组中添加缓存
         * - 用于向数组缓存中添加数据
         * - Redis::push( $key:string(键名), $value:any(值), $expire|false:number(过期时间) )
         * - return boolean 设置结果
         */
        public static function push( $key, $value, $expire = false ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $key = self::name( $key );
                $run = self::$ref->rpush( $key, self::filter( $value ) );
                // 是否增加过期时间
                if ( is_numeric( $expire ) ) {
                    self::$ref->expire( $key, $expire );
                }
                // 返回结果
                return $run ? true : false;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 查询数组缓存
         * - 用于查询数组类的缓存
         * - Redis::array( $key:string(键名), $index|false:array(数组下标) )
         * - return any 查询结果
         */
        public static function array( $key, $index = false ) {
            if ( ( $index !== false && !is_array( $index ) ) || self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $key = self::name( $key );
                if ( $index === false ) { $index = [ 0, -1 ]; }
                $data = self::$ref->lrange( $key, $index[0], $index[1] );
                if ( is_array( $data ) ) {
                    foreach( $data as $dataKey => $dataValue ) {
                        $data[$dataKey] = self::output( $dataValue );
                    }
                    return $data;
                }
                return false;
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 消费数组缓存
         * - 用于在数组中依次消费数据
         * - Redis::lpop( $key:string(键名) )
         * - return any 本次消费内容
         */
        public static function lpop( $key ) {
            if ( self::register() === false ) { return false; }
            // 处理逻辑
            try {
                $key = self::name( $key );
                return self::output( self::$ref->lpop( $key ) );
            }catch ( Throwable $e ) {
                return self::log( $e );
            }
        }
        /**
         * 操作临时缓存
         */
        public static $cacheName = "cache_";
        public static function getCache( $key ) { return self::get( self::$cacheName.$key ); }
        public static function setCache( $key, $value, $expire = false ) { return self::set( self::$cacheName.$key, $value, $expire ); }
        public static function delCache( $key ) { return self::del( self::$cacheName.$key ); }
    }