<?php
    /**
     * HTTP 服务端入口
     */

    // 重置工作目录
    chdir( dirname( getcwd() ) );
    // 运行核心
    require_once 'support/Bootstrap.php';
    echo \Support\Bootstrap::build( 'http', function(){
        return \Support\Provider\Http::init();
    });