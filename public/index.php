<?php
    /**
     * 重置工作路径
     */
    chdir( dirname( getcwd() ) );
    /**
     * 运行核心
     */
    require_once 'loader/core.loader.php';
    new core(function(){
        import( 'loader/web.loader.php' );
        return task::start();
    });