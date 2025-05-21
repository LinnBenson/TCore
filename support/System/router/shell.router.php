<?php

use Support\Handler\Router;

    // 主菜单
    Router::add( '/' )->controller( 'ShellController@menu' )->save();
    // 访问插件
    Router::add( '/plug/{{parameter?}}' )->controller( 'ShellController@plug' )->save();
    // 打印版本号
    Router::add( '/version' )->to(function(){ return "TCore Version ".config( 'app.version' ).PHP_EOL; })->save();