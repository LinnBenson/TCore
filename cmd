#!/usr/bin/env php

<?php
    /**
     * 运行核心
     */
    require_once 'loader/core.loader.php';
    new core(function()use ( $argv ){
        import( 'loader/cmd.loader.php' );
        return task::start( $argv );
    });
?>