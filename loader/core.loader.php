<?php

use application\server\serviceServer;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use support\method\RE;
use WebSocket\Client;

    class core {
        // 系统运行缓存
        public static $cache = [ 'autoload' => [], 'config' => [] ];
        // 语言包
        public static $text = [];
        public static $textLang = false;
        // OEM
        public static $db;
        /**
         * 构造函数
         */
        public function __construct( $task = false ) {
            // 引用全局函数
            require_once 'support/method/global.method.php';
            // 加载 Composer
            require 'support/vendor/autoload.php';
            // 动态加载
            spl_autoload_register( function( $class ) {
                if ( isset( self::$cache['autoload'][$class] ) && file_exists( self::$cache['autoload'][$class] ) ) {
                    include self::$cache['autoload'][$class];
                }
            });
            // 加载 .env 文件
            try {
                $dotenv = Dotenv::createImmutable( dirname( __DIR__ ) );
                $dotenv->load();
            }catch( Exception $e ) {
                exit( "A serious error occurred: ".$e->getMessage() );
            }
            // 启用性检查
            if ( !config( 'app.enable' ) ) { exit( 'Access Denied.' ); }
            // 修改 PHP 配置信息
            if ( config( 'app.debug' ) ) {
                error_reporting( E_ERROR | E_WARNING | E_PARSE );
                ini_set( 'display_errors', 1 );
            }else {
                error_reporting( 0 ); ini_set( 'display_errors', 0 );
            }
            date_default_timezone_set( config( 'app.timezone' ) );
            // 挂载语言包
            self::loadLang();
            // 挂载 OEM
            try {
                self::$db = new Capsule;
                self::$db->addConnection( config( 'database.mysql' ) );
                self::$db->setAsGlobal();
                self::$db->bootEloquent();
            }catch ( Exception $e ) {
                self::$db = '';
                self::log( [ t('error.router'), $e ], 'core' );
            }
            // 挂载动态加载
            self::autoload( config( 'autoload.core' ) );
            self::autoload( config( 'autoload.model' ) );
            // 运行任务
            if ( is_callable( $task ) ) {
                import( 'loader/router.loader.php' );
                $show = $task();
                echo is_string( $show ) ? $show : '';
            }
        }
        /**
         * 挂载动态加载
         * - $data array 挂载配置
         * ---
         * return boolean 挂载结果
         */
        public static function autoload( $data = [] ) {
            self::$cache['autoload'] = array_merge( self::$cache['autoload'], $data );
            return true;
        }
        /**
         * 挂载语言包
         * - $set string 语言
         * ---
         * return boolean 挂载结果
         */
        public static function loadLang( $set = null ) {
            $textLang = config( 'app.lang' );
            if ( !empty( $set ) && file_exists( "storage/lang/{$set}.lang.php" ) ) { $textLang = $set; }
            if ( $textLang === self::$textLang ) { return true; }
            $langFile = "storage/lang/{$textLang}.lang.php";
            if ( file_exists( $langFile ) ) {
                self::$text = import( $langFile );
                self::$textLang = $textLang;
                return true;
            }
            return false;
        }
        /**
         * 记录运行日志
         * - $val any 需要记录的内容
         * - $logFile string 指定需要使用的日志文件
         * ---
         * return boolean 记录结果
         */
        public static function log( $val, $logFile = 'debug' ) {
            // 确保日志目录存在
            if ( !is_dir( 'storage/log/' ) ) { mkdir( 'storage/log/' ); }
            // 读取日志配置
            $config = config( "log.{$logFile}" );
            if ( !is_array( $config ) ) { return self::log( "[{$logFile}] ".t( 'error.nullLog' ) ); }
            // 写入文件
            $valTitle = '';
            if ( is_array( $val ) && count( $val ) === 2 && is_object( $val[1] ) ) { $valTitle = $val[0]; $val = $val[1]; }
            if ( is_object( $val ) && method_exists( $val, 'getMessage' ) ) { $val = $val->getMessage(); }
            $val = toString( $val );
            $val = !empty( $valTitle ) ? "{$valTitle}\n{$val}" : $val;
            $val = "[ ".date('Y-m-d H:i:s')." ]\n{$val}\n\n";
            if ( file_exists( $config['file'] ) ) {
                if ( filesize( $config['file'] ) < $config['maxSize'] ) {
                    return file_put_contents( $config['file'], $val, FILE_APPEND|LOCK_EX );
                }
            }
            // 其它情况直接覆盖或创建文件
            return file_put_contents( $config['file'], $val );
        }
        /**
         * 抛出一个错误
         * - $data any 错误信息
         * ---
         * return null
         */
        public static function error( $data ) {
            throw new Exception( $data );
        }
        /**
         * 执行异步任务
         * - $name string 服务项名称
         * - $action string 传递方法
         * - $res array 传递数据
         * - $thread number 执行线程
         * ---
         * return boolean 执行结果
         */
        public static function async( $name, $action, $res = [], $thread = false ) {
            // 查询服务项
            $config = serviceServer::getAllService();
            if ( $config['async']['state'] === false ) {
                return false;
            }
            if ( !empty( $config[$name] ) && $config[$name]['state'] === false ) {
                return false;
            }
            // 组织数据结构
            $data = [
                'action' => 'send_channel',
                'res' => [
                    'service' => $name,
                    'action' => $action,
                    'res' => $res
                ]
            ];
            // 随机线程
            if ( $thread === false ) {
                if ( !empty( $config[$name] ) && is_numeric( $config[$name]['thread'] ) && $config[$name]['thread'] > 1 ) {
                    $thread = mt_rand( 1, $config[$name]['thread'] );
                }
            }
            if ( is_numeric( $thread ) ) { $data['res']['thread'] = $thread; }
            // 尝试连接到服务器
            try {
                $link = "ws://0.0.0.0:{$config['async']['port']}";
                $link = new Client( $link );
                $link->send( json_encode( $data, JSON_UNESCAPED_UNICODE ));
                $link->close();
                return true;
            }catch ( Exception $e ) {
                self::log( $e, 'core' );
            }
            return false;
        }
        /**
         * 检查用户是否在线
         * - $type string 检查类型
         * - $user string 检查用户
         * return boolean 检查结果
         */
        public static function online( $type, $user ) {
            $check = RE::getCache( "online_{$type}_".$user );
            return !empty( $check ) ? true : false;
        }
    }