<?php

use Support\Provider\HttpProvider;

    /**
     * HTTP 服务端入口
     */
    chdir( dirname( getcwd() ) );
    /**
     * 运行核心
     */
    require_once 'support/Bootstrap.php';
    echo Bootstrap::build(function(){
        return HttpProvider::start();
    });