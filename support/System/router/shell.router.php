<?php
    use Support\Handler\Router;

    /**
     * 命令行接口
     */
    Router::add( '/' )->controller( 'ShellController@menu' )->save();
    /**
     * 命令行调试接口
     */
    Router::add( '/debug' )->controller( 'BaseController@debug' )->save();