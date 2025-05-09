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
            return is_callable( $method ) ? $method() : null;
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
            try {
                $dotenv = Dotenv::createImmutable( getcwd() );
                $dotenv->load();
            }catch( Exception $e ) {
                exit( "A serious error occurred: ".$e->getMessage() );
            }
        }
    }