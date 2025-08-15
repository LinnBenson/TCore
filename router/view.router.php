<?php
    use Support\Handler\Router;

    /**
     * 欢迎页面
     */
    Router::add( '/' )->view( 'welcome' )->save();