<?php
use loader\user;

    class task {
        // 当前用户
        public static $user = null;
        /**
         * 启动
         */
        public static function start() {
            // usleep( 400000 );
            $access = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            if ( preg_match( '#^/(api)#', $access ) ) {
                router::$type = 'api';
                $access = preg_replace( '/^\/(api|app)/', '', $access );
            }else if ( preg_match( '#^/(storage)#', $_SERVER['REQUEST_URI'] ) ) {
                router::$type = 'api';
            }else if ( preg_match( '#^/(app)#', $_SERVER['REQUEST_URI'] ) ) {
                router::$type = 'app';
                $access = preg_replace( '/^\/(api|app)/', '', $access );
            }else {
                router::$type = 'view';
            }
            self::$user = new user( 'web' );
            $result = router::start( $access );
            if ( is_numeric( self::$user->code ) && !headers_sent() ) {
                http_response_code( self::$user->code );
                self::$user->code = false;
            }
            return $result;
        }
        /**
         * 输出结果
         * - $type string 输出类型
         * - $state any 修改结果
         * ---
         * return string 结果
         */
        public static function result( $type, $state ) {
            return task::echo(
                !empty( $state ) || $state === '0' || $state === 0 ? 0 : 2,
                [!empty( $state ) ? 'true' : 'false',['type' => $type]]
            );
        }
        /**
         * 输出函数
         * - state number 回调状态
         * - $content string 回调内容
         * - $code number 响应代码
         * ---
         * return json
         */
        public static function echo( $state, $content, $code = 200 ) {
            $check = false;
            if ( is_array( $content ) && count( $content ) <= 2 ) { $check = t( $content[0], $content[1] ); }
            if ( !empty( $check ) && $check !== $content[0] ) { $content = $check; }
            $result = array();
            switch ( $state ) {
                case 0: $result['s'] = 'success'; break;
                case 1: $result['s'] = 'fail'; break;
                case 2: $result['s'] = 'error'; break;
                case 3: $result['s'] = 'warn'; break;
                default: $result['s'] = 'unknown'; break;
            }
            $code = !empty( $code ) && is_numeric( $code ) ? $code : 200;
            if ( is_numeric( self::$user->code ) ) { $code = self::$user->code; self::$user->code = false; }
            $result['c'] = $code;
            $result['t'] = time();
            $result['d'] = $content;
            if ( !headers_sent() ) { http_response_code( $code ); }
            header( 'Content-Type: application/json' );
            return json_encode( $result, JSON_UNESCAPED_UNICODE );
        }
    }