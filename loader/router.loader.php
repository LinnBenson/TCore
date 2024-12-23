<?php
use support\middleware\access;
use support\middleware\view;

    class router {
        // 路由类型
        public static $type = '';
        public static $argv = '';
        // 路由配置
        public static $config = [];
        /**
         * 路由开始
         * - $argv string 路由查询参数
         * - $sendData array 发送数据
         * ---
         * return any 路由搜索结果
         */
        public static function start( $argv = '', $sendData = false ) {
            self::$argv = $argv = !empty( $argv ) ? $argv : '/';
            if ( empty( self::$config ) ) {
                $config = config( 'router.'.self::$type );
                // 路由类型错误
                if ( !is_array( $config ) ) {
                    core::log( "[ ".self::$type." ] ".t( 'error.typeRouter' ), 'core' );
                    $config = [];
                }
                // 引用路由文件
                import( $config );
            }
            return self::search( $argv, $sendData );
        }
        /**
         * 路由搜索
         * - $argv string 路由查询参数
         * - $sendData array 发送数据
         * - $record array 记录用户覆盖数据
         * ---
         * return any 路由搜索结果
         */
        public static function search( $argv, $sendData = false, $record = [] ) {
            self::$argv = $argv = !empty( $argv ) ? $argv : '/';
            $method = function() { return self::error( 404 ); };
            // 简单查询
            if ( is_callable( self::$config[$argv] ) ) {
                $access = access::check( $record );
                access::record( $argv, $record, $access );
                if ( $access ) {
                    $method = self::$config[$argv];
                }else {
                    $method = function() { return self::error( 500, t('error.limit') ); };
                }
            // 模糊查询
            }else {
                $replacements = array(
                    '/'       => '\\/',
                    '\/*'     => '(\/.*)?',
                    '{all}'   => '[a-zA-Z0-9]+',
                    '{letter}'=> '[a-zA-Z]+',
                    '{num}'   => '\d+'
                );
                foreach( self::$config as $path => $pathMethod ) {
                    $path = str_replace( array_keys( $replacements ), array_values( $replacements ), $path );
                    if ( preg_match( "/^{$path}$/", $argv ) && is_callable( $pathMethod ) ) {
                        $access = access::check( $record );
                        access::record( $argv, $record, $access );
                        if ( $access ) {
                            $method = $pathMethod;
                        }else {
                            $method = function() { return self::error( 500, t('error.limit') ); };
                        }
                        break;
                    }
                }
            }
            try {
                core::autoload( config( 'autoload.server' ) );
                return $method( explode( '/', $argv ), $sendData );
            }catch ( Exception $e ) {
                $error = $e->getMessage();
                if ( is_json( $error ) ) {
                    return $error;
                }else {
                    core::log( ["[ ".self::$argv." ] ".t( 'error.router' ), $e], 'core' );
                    if ( config( 'app.debug' ) ) {
                        return self::error( 500, $e->getMessage() );
                    }
                    return self::error( 500, t('error.500') );
                }
            }
        }
        /**
         * 注册路由
         * - $name string 匹配路径
         * - $type string 匹配类型
         * - $add string 在匹配路径前添加值
         * ---
         * return ovject 路由配置实体
         */
        public static function add( $name, $type = 'ANY', $add = '' ) {
            // 设定实体
            import( 'support/transfer/router.transfer.php' );
            $ref = new routerRegister();
            // 数据整理
            $enable = false;
            $type = strtoupper( $type );
            $allow = [ 'GET', 'POST', 'UPDATE', 'DELETE' ];
            // 检查路由
            if ( in_array( $type, $allow ) ) {
                if ( $_SERVER['REQUEST_METHOD'] === $type ) { $enable = true; }
            }else {
                if (
                    self::$type === 'api' ||
                    self::$type === 'app' ||
                    self::$type === 'view' ||
                    self::$type === 'cmd' ||
                    strpos( self::$type, "service_" ) === 0 ||
                    self::$argv === $name
                ) {
                    $enable = true;
                }
                $type = 'ANY';
            }
            // 返回实体
            return $ref->setRef( $enable, "{$add}{$name}", $ref );
        }
        /**
         * 输出错误
         * - $code number 错误类型
         * - $content string 错误内容
         * ---
         * return string 错误信息
         */
        public static function error( $code, $content = '' ) {
            if ( empty( $content ) ) { $content = t( 'error.404' ); }
            switch ( self::$type ) {
                case 'view': return view::show( 'system/error', [ 'code' => $code, 'content' => $content ] );

                default: return task::echo( 2, $content, $code );
            }
        }
    }