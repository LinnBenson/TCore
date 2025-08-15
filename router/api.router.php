<?php
    use Support\Handler\Router;

    /**
     * 前台调试端口
     */
    Router::add( '/debug' )->controller( 'BaseController@debug' )->save();