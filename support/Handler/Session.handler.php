<?php
    /**
     * Session 操作器
     */
    namespace Support\Handler;

    class Session {
        public static $init = false; // 是否初始化
        /**
         * 初始化 Session
         * - 使用 Session 时会自动调用此方法
         * - @return boolean 初始化结果
         */
        public static function init() {
            if ( self::$init ) { return true; }
            ini_set( 'session.save_path', inFolder( config( 'database.session.folder' ) ) );
            ini_set( 'session.gc_maxlifetime', config( 'database.session.expired' ) );
            session_name( config( 'database.session.name' ) );
            session_set_cookie_params([
                'lifetime' => config( 'database.session.expired' ),
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httponly' => true
            ]);
            session_start();
            self::$init = true;
            return true;
        }
        /**
         * 获取 Session
         * - 键名为 null 时返回所有 Session
         * - @param string $key 键名
         * - @return mixed Session 值
         */
        public static function get( $key = null ) {
            self::init();
            if ( is_null( $key ) ) { return $_SESSION; }
            $result = isset( $_SESSION[$key] ) ? $_SESSION[$key] : null;
            if ( is_json( $result ) ) { $result = json_decode( $result, true ); }
            return $result;
        }
        /**
         * 删除 Session
         * - 用于删除 Session
         * - @param string $key 键名
         * - @return boolean 删除结果
         */
        public static function del( $key ) {
            self::init();
            if ( isset( $_SESSION[$key] ) ) { unset( $_SESSION[$key] ); }
            return true;
        }
        /**
         * 设置 Session
         * - 值为数组时会自动转换为 JSON
         * - @param string $key 键名
         * - @param mixed $value 值
         * - @return boolean 设置结果
         */
        public static function set( $key, $value ) {
            self::init();
            if ( is_array( $value ) ) { $value = json_encode( $value ); }
            $_SESSION[$key] = $value;
            return true;
        }
        /**
         * 重置 Session
         * - 清空 Session
         * - @return boolean 重置结果
         */
        public static function reset() {
            self::init();
            session_destroy();
            return true;
        }
    }