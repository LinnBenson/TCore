<?php

use Dotenv\Dotenv;

    class Bootstrap {
        public static $cache = array( 'config' => [] ); // 缓存
        /**
         * 构造函数
         * new Bootstrap( $callback|false:function(回调函数) )
         */
        public function __construct( $callback = false ) {
            // 初始化程序
            $this->initialization();
            // 输出回调内容
            $this->echo(
                MainProcess::BootstrapCallback( $callback )
            );
        }
        /**
         * 输出回调内容
         */
        private function echo( $callback ) {
            if ( is_callable( $callback ) ) {
                $echo = $callback();
                if ( is_string( $echo ) ) { echo $echo; }
            }
            return false;
        }
        /**
         * 初始化程序
         */
        private function initialization() {
            // 基础依赖加载
            require_once 'support/Helper/global.helper.php';
            require_once 'support/Helper/system.helper.php';
            require_once 'app/Bootstrap/MainProcess.php';
            // 加载 Composer
            import( 'vendor/autoload.php' );
            // 加载 .env 文件
            try {
                $dotenv = Dotenv::createImmutable( getcwd() );
                $dotenv->load();
            }catch( Exception $e ) {
                exit( "A serious error occurred: ".$e->getMessage() );
            }
            // 调整 PHP 配置
            if ( config( 'app.debug' ) ) {
                error_reporting( E_ERROR | E_WARNING | E_PARSE );
                ini_set( 'display_errors', 1 );
            }else {
                error_reporting( 0 ); ini_set( 'display_errors', 0 );
            }
            date_default_timezone_set( config( 'app.timezone' ) );
            // 自动加载应用
            $this->autoload();
        }
        /**
         * 自动加载应用
         */
        private function autoload() {

        }
    }