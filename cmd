#!/usr/bin/env php


<?php
use Support\Provider\Shell;

    /**
     * 命令行服务端入口
     */

    // 运行核心
    require_once 'support/Bootstrap.php';
    echo \Support\Bootstrap::build( 'http', function()use ( $argv ){
        return \Support\Provider\Shell::init( $argv );
    });