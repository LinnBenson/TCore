<?php
use support\middleware\view;

    // 管理员后台
    router::add( '/admin', 'any' )->children(function( $e ) {
        router::add( '/*', 'any', $e )
            ->controller( 'admin/view', 'index' )
            ->auth(function(){ return !task::$user->authLevel([ 600, 1000 ]) ? view::link( '/account/login?pass=/admin/index&logout=1' ) : true; })
            ->start( 2 )->save();
    });
    // 账户登录
    router::add( '/account' )
        ->link( 'account/login' )
        ->auth(function(){ return task::$user->state ? view::link( '/account/user' ) : true; })
        ->children(function( $e ) {
            router::add( '/login', 'GET', $e )->view( 'account/login' )->auth(function(){ return task::$user->state && empty( $_GET['logout'] ) ? view::link( '/account/user' ) : true; })->save();
            router::add( '/register', 'GET', $e )->view( 'account/register' )->auth(function(){ return task::$user->state && empty( $_GET['logout'] ) ? view::link( '/account/user' ) : true; })->save();
            router::add( '/retrieve', 'GET', $e )->view( 'account/retrieve' )->auth(function(){ return task::$user->state && empty( $_GET['logout'] ) ? view::link( '/account/user' ) : true; })->save();
            router::add( '/user', 'GET', $e )->view( 'account/user' )->auth(function(){ return !task::$user->state ? view::link( '/account/login' ) : true; })->save();
    });
    // 主链接
    router::add( '/' )->link( '/account' )->save();