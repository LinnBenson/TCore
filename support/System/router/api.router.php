<?php
    use Support\Handler\Router;

    /**
     * 系统信息获取
     */
    Router::add( '/base/info{{type?}}' )->controller( 'BaseController@info' )->save();
    /**
     * 账户管理接口
     */
    Router::add( '/account/login' )->controller( 'AccountController@login' )->save();