<?php

use Dotenv\Dotenv;

    class Bootstrap {
        // 应用缓存
        public static $cache = [];
        /**
         * 构建应用
         * - 用于构建并初始化应用，此方法只需在启动应用时调用一次
         * - @param function $method 传入方法，默认为 null
         * - @return mixed 回调结果
         */
        public static function build( $method = null ) {
            // 初始化应用
            self::init();
            // 执行用户传入的方法
            return config( 'process.OutputReturnResult' )(
                is_callable( $method ) ? $method() : null
            );;
        }
        /**
         * 初始化应用
         * - 用于初始化应用，通常在应用启动时调用
         * - @return void
         */
        private static function init() {
            // 导入基础依赖
            require_once 'support/Helper/Global.helper.php';
            require_once 'support/Helper/System.helper.php';
            // 加载 Composer
            import( 'vendor/autoload.php' );
            // 加载 .env 文件
            $_ENV = self::cache( 'file', 'env.php', function(){
                try {
                    $dotenv = Dotenv::createImmutable( getcwd() );
                    $dotenv->load();
                }catch( Exception $e ) {
                    exit( "A serious error occurred: ".$e->getMessage() );
                }
                return $_ENV;
            });
            if ( !env( 'APP_ENABLE', false ) ) { exit( 'This application is currently closed.' ); }
            // 调整 PHP 配置
            if ( config( 'app.debug' ) ) {
                error_reporting( E_ERROR | E_WARNING | E_PARSE );
                ini_set( 'display_errors', 1 );
            }else {
                error_reporting( 0 ); ini_set( 'display_errors', 0 );
            }
            date_default_timezone_set( config( 'app.timezone' ) );
            // 自动加载应用
            Bootstrap::$cache['autoload'] = [];
            spl_autoload_register( function( $class ) {
                if ( isset( Bootstrap::$cache['autoload'][$class] ) ) {
                    import( Bootstrap::$cache['autoload'][$class] );
                    return true;
                }else if ( startsWith( $class, 'App\\' ) ) {
                    $class = explode( '\\', $class );
                    $class[0] = strtolower( $class[0] ); // App
                    import( implode( '/', $class ).".".strtolower( $class[1] ).".php" );
                    return true;
                }
                return false;
            });
            // 用户配置流程
            config( 'process.InitializationCompleted' )();
        }
        /**
         * 获取应用缓存
         * - 用于获取和设置应用缓存，名称以 php 结尾则以 php 保存，否则保存为 txt ，加载方法可以输出一段代码或文本或数组
         * - @param string $type 缓存类型
         * - @param string $name 缓存名称
         * - @param mixed $load 加载方法，默认为 null
         * - @return mixed 缓存结果
         */
        public static function cache( $type, $name, $load = null ) {
            if ( $type === 'thread' ) {
                if ( isset( self::$cache[$name] ) ) { return self::$cache[$name]; }
                if ( is_callable( $load ) ) {
                    self::$cache[$name] = $load();
                    return self::$cache[$name];
                }
            }
            if ( $type === 'file' ) {
                if ( env( 'APP_DEBUG', false ) === true ) { return $load(); }
                $type = endsWith( $name, 'php' ) ? 'php' : 'txt';
                $name = h( $name );
                $file = inFolder( "storage/cache/Bootstrap/{$name}.{$type}" );
                if ( file_exists( $file ) ) { return $type === 'php' ? require $file : file_get_contents( $file ); }
                if ( is_callable( $load ) ) {
                    $result = $load();
                    if ( $type === 'php' ) {
                        if ( is_array( $result ) ) {
                            file_put_contents( $file, "<?php\nreturn ".var_export( $result, true ).";\n" );
                        }else {
                            file_put_contents( $file, $result ); $result = require $file;
                        }
                        return $result;
                    }
                    file_put_contents( $file, $result );
                    return $result;
                }
            }
            return null;
        }
        /**
         * 写入日志
         * - 用于写入核心程序执行日志
         * - @param string $title 日志标题
         * - @param string $content 日志内容
         * - @return boolean 写入结果
         */
        public static function log( $title, $content ) {
            $file = inFolder( 'storage/log/Bootstrap_'.date( 'Ymd' ).'.log' );
            // 传入为对象时认为是异常对象
            if ( is_object( $content ) && method_exists( $content, 'getMessage' ) ) {
                $content = $content->getMessage();
            }
            // 再次检查是否为字符串
            if ( !is_string( $content ) ) { $content = print_r( $content, true ); }
            // 准备写入内容
            $content = date( 'H:i:s' )." {$title} | {$content}\n";
            return file_put_contents( $file, $content, FILE_APPEND );
        }
        /**
         * 抛出一个错误
         * - 用于抛出一个错误，通常用于调试或异常处理
         * - @param string $data 错误信息
         * - @throws Exception 抛出异常
         */
        public static function error( $data ) { throw new Exception( $data ); }
        /**
         * 注册自动加载
         * - 用于注册自动加载类文件
         * - @param array $config 自动加载配置
         * - @return boolean 注册结果
         */
        public static function autoload( $config ) {
            if ( !is_array( $config ) ) { return false; }
            Bootstrap::$cache['autoload'] = array_merge( Bootstrap::$cache['autoload'], $config );
            return true;
        }
    }