<?php
    /**
     * 应用配置文件
     */
    return [
        /**
         * 启用程序
         */
        'enable' => (bool) env( 'APP_ENABLE', false ),
        /**
         * 应用名称
         */
        'name' => env( 'APP_NAME', 'TCore' ),
        /**
         * 应用标题
         */
        'title' => env( 'APP_TITLE', env( 'APP_NAME', 'TCore' ) ),
        /**
         * 主机地址
         */
        'host' => env( 'APP_HOST', 'localhost' ),
        /**
         * 语言设置
         */
        'lang' => env( 'APP_LANG', 'en' ),
        /**
         * 调试模式
         */
        'debug' => (bool) env( 'APP_DEBUG', false ),
        /**
         * 运行时区
         */
        'timezone' => env( 'APP_TIMEZONE', 'UTC' ),
        /**
         * 应用版本
         */
        'version' => '1.0.0',
        /**
         * 视图渲染器
         */
        'renderer' => 'ViewRenderer'
    ];