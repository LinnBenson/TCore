<?php
namespace support\method;

/**
 * ---
 * Redis 操作工具
 * ---
 */

use core;
use Redis;

    class RE {
        public static $ref = null;
        /**
         * 连接到 Redis
        * ---
        * return boolean
        */
        private static function link() {
            // 扩展检查
            if ( !extension_loaded( 'redis' ) ) {
                core::log( 'Redis is not installed.', 'redis' );
                return false;
            }
            try {
                // 尝试建立连接
                $config = config( 'database.redis' );
                self::$ref = new Redis();
                self::$ref->connect( $config['host'], $config['port'] );
                if ( !empty( $config['password'] ) ) {
                    self::$ref->auth( $config['password'] );
                }
                return self::$ref->ping() !== false ? true : false;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 检查连接状态
        * ---
        * return boolean
        */
        public static function check() {
            // 还没开始过连接
            if ( empty( self::$ref ) ) { return self::link(); }
            // 简单查询检查连接
            try {
                return self::$ref->ping() !== false ? true : self::link();
            }catch ( PDOException $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 手动关闭连接
        * ---
        * return boolean
        */
        public static function close() {
            if ( is_object( self::$ref ) ) {
                self::$ref->close();
            }
            return true;
        }
        /**
         * 添加前缀
        * - $text string 待处理的名称
        * ---
        * return string
        */
        public static function name( $text ) {
            $config = config( 'database.redis' );
            if ( substr( $text, 0, strlen( $config['prefix'] ) ) === $config['prefix'] ) {
                return $text;
            }
            return $config['prefix'].$text;
        }
        /**
         * 写入检查
        * --- $text string 待写入的内容
        * ---
        * return string
        */
        public static function filter( $text ) {
            $text = str_replace( ["\0", "\r", "\n", "\x08", "\x0c", ":", "*", "?"], '', $text );
            return $text;
        }
        /**
         * 增加或更新一个值
        * - $db number 操作的库
        * - $key string 键名
        * - $value string/array 对应内容
        * - $expire number(false) 过期时间 （秒）
        * ---
        * return boolean
        */
        public static function set( $db, $key, $value, $expire = false ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) || // 键名必须是字符串
                ( !is_array( $value ) && !is_string( $value ) && !is_numeric( $value ) ) || // 保存数据必须是数组或者字符串
                ( !empty( $expire ) && !is_numeric( $expire ) ) // 如果有过期时间则必须为数字
            ) { return false; }
            // 加工键名
            $newKey = self::name( self::filter( $key ) );
            try {
                if ( is_string( $value ) ) { $value = addslashes( $value ); }
                if ( is_array( $value ) ) { $value = json_encode( $value ); }
                // 设置对应值
                self::$ref->select( $db );
                $set = self::$ref->set( $newKey, $value );
                // 是否增加过期时间
                if ( is_numeric( $expire ) ) {
                    self::$ref->expire( $newKey, $expire );
                }
                // 返回设置结果
                return $set ? true : false;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 给一个值添加过期时间
        * - $db number 操作的库
        * - $key string 键名
        * - $expire number(64800) 过期时间 （秒）
        * ---
        * return boolean
        */
        public static function expire( $db, $key, $expire = 64800 ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) || // 键名必须是字符串
                ( !empty( $expire ) && !is_numeric( $expire ) ) // 如果有过期时间则必须为数字
            ) { return false; }
            try {
                // 赋予过期时间
                self::$ref->select( $db );
                return self::$ref->expire( self::name( self::filter( $key ) ), $expire ) ? true : false;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 获取一个值
        * - $db number 操作的库
        * - $key string 键名
        * ---
        * return false/string/array
        */
        public static function get( $db, $key ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) // 键名必须是字符串
            ) { return false; }
            try {
                // 获取对应值
                self::$ref->select( $db );
                $data = self::$ref->get( self::name( self::filter( $key ) ) );
                $data = stripslashes( $data );
                if ( is_json( $data ) ) { $data = json_decode( $data, true ); }
                if ( is_numeric( $data ) ) { $data = floatval( $data ); }
                return $data;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 删除一个值
        * - $db number 操作的库
        * - $key string 键名
        * ---
        * return boolean or number
        */
        public static function delete( $db, $key ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) // 键名必须是字符串
            ) { return false; }
            try {
                // 删除对应值
                self::$ref->select( $db );
                $run = self::$ref->del( self::name( self::filter( $key ) ) );
                return is_numeric( $run ) ? $run : false;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 获取指定开始名的值
        * - $db number 操作的库
        * - $key string 键名
        * ---
        * return boolean or data
        */
        public static function getAll( $db, $key ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) // 键名必须是字符串
            ) { return false; }
            try {
                // 获取所有以此名开头的值
                self::$ref->select( $db );
                $keys = self::$ref->keys( self::name( self::filter( $key ) ).'*' );
                if ( !$keys ) { return $keys; }
                $data = array();
                foreach ( $keys as $key ) {
                    $data[$key] = self::$ref->get( $key );
                    $data[$key] = stripslashes( $data[$key] );
                    if ( is_json( $data[$key] ) ) { $data[$key] = json_decode( $data[$key], true ); }
                    if ( is_numeric( $data[$key] ) ) { $data[$key] = floatval( $data[$key] ); }
                }
                return $data;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 删除指定开始名的值
        * - $db number 操作的库
        * - $key string 键名
        * ---
        * return false/number
        */
        public static function delAll( $db, $key ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) // 键名必须是字符串
            ) { return false; }
            try {
                // 删除所有以此名开头的值
                self::$ref->select( $db );
                $keys = self::$ref->keys( self::name( self::filter( $key ) ).'*' );
                if ( !empty( $keys ) ) {
                    $run = self::$ref->del( $keys );
                    return is_numeric( $run ) ? $run : false;
                }
                return 0;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 向数组键值中添加内容
        * - $db number 操作的库
        * - $key string 键名
        * - $value array 对应内容
        * ---
        * return boolean
        */
        public static function push( $db, $key, $value ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) || // 键名必须是字符串
                ( !is_array( $value ) && !is_string( $value ) && !is_numeric( $value ) ) // 保存数据必须是数组或者字符串
            ) { return false; }
            self::$ref->select( $db );
            // 加工键名
            $newKey = self::name( self::filter( $key ) );
            try {
                if ( is_array( $value ) ) { $value = json_encode( $value ); }
                $run = self::$ref->rpush( $newKey, $value );
                if ( $run === false ) { return false; }
                return true;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 获取数组键值
        * - $db number 操作的库
        * - $key string 键名
        * - $index array(false) 数组下标
        * ---
        * return any 查询结果
        */
        public static function show( $db, $key, $index = false ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) || // 键名必须是字符串
                ( $index !== false && !is_array( $index ) ) // 下标必须为数字数组
            ) { return false; }
            self::$ref->select( $db );
            // 加工键名
            $newKey = self::name( self::filter( $key ) );
            if ( $index === false ) { $index = [ 0, -1 ]; }
            try {
                $data = self::$ref->lrange( $newKey, $index[0], $index[1] );
                if ( $data === false ) { return false; }
                return $data;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 消费数据
        * - $db number 操作的库
        * - $key string 键名
        * ---
        * return any 查询结果
         */
        public static function lpop( $db, $key ) {
            if ( !self::check() ) { return false; }
            // 参数检查
            if (
                !is_numeric( $db ) || // 选择库必需是数字
                !is_string( $key ) // 键名必须是字符串
            ) { return false; }
            self::$ref->select( $db );
            // 加工键名
            $newKey = self::name( self::filter( $key ) );
            try {
                $data = self::$ref->lpop( $newKey );
                if ( is_json( $data ) ) { $data = json_decode( $data, true ); }
                if ( is_numeric( $data ) ) { $data = floatval( $data ); }
                return $data;
            }catch ( Exception $e ) {
                // 捕获错误
                core::log( $e, 'redis' );
                return false;
            }
        }
        /**
         * 设置缓存
        * - $key string 键名
        * - $value string/array 对应内容
        * - $expire number(false) 过期时间 （秒）
        * ---
        * return boolean
        */
        public static function setCache( $key, $value, $expire = false ) {
            return self::set( 0, "cache_{$key}", $value, $expire );
        }
        /**
         * 查询缓存
        * - $key string 键名
        * ---
        * return false/string/array
        */
        public static function getCache( $key ) {
            return self::get( 0, "cache_{$key}" );
        }
    }